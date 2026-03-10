<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CrmDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Verificar si existen estados para el conjunto actual
        $hasStatuses = LeadStatus::exists();

        // Si no hay estados, redirigir a vista con mensaje
        if (!$hasStatuses) {
            return view('crm.dashboard-no-statuses');
        }

        // Determinar si el usuario puede ver todos los leads
        $canViewAll = $user->can('view.crm.all-leads');

        // Usuarios con permiso para ver todos los leads
        if ($canViewAll) {
            $leadsQuery = Lead::query();
        } else {
            // Usuarios regulares solo ven sus leads asignados
            if ($user->vendedor_id) {
                $leadsQuery = Lead::where('vendedor_id', $user->vendedor_id);
            } else {
                // Si no tiene vendedor asignado, query vacío
                $leadsQuery = Lead::whereRaw('1 = 0');
            }
        }

        // Estadísticas generales
        $stats = [
            'total_leads' => $leadsQuery->count(),
            'nuevos_leads' => (clone $leadsQuery)->whereHas('status', function($q) {
                $q->where('name', 'Nuevo');
            })->count(),
            'leads_activos' => (clone $leadsQuery)->whereHas('status', function($q) {
                $q->whereNotIn('name', ['Ganado', 'Perdido']);
            })->count(),
            'leads_ganados' => (clone $leadsQuery)->whereHas('status', function($q) {
                $q->where('name', 'Ganado');
            })->whereMonth('updated_at', Carbon::now()->month)->count(),
        ];

        // Leads por estado
        $leadsByStatusRaw = (clone $leadsQuery)
            ->select('lead_status_id', DB::raw('count(*) as total'))
            ->groupBy('lead_status_id')
            ->get();

        $leadsByStatusArray = [];
        foreach ($leadsByStatusRaw as $item) {
            $status = LeadStatus::find($item->lead_status_id);
            if ($status) {
                $leadsByStatusArray[$status->name] = $item->total;
            }
        }
        $leadsByStatus = collect($leadsByStatusArray);

        // Leads por prioridad
        $leadsByPriority = (clone $leadsQuery)
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(function($item) {
                $priorityName = match($item->priority) {
                    1 => 'Alta',
                    2 => 'Media',
                    3 => 'Baja',
                    default => 'Sin definir',
                };
                return [$priorityName => $item->total];
            });

        // Actividad de los últimos 7 días
        if ($canViewAll) {
            // Ver todas las actividades
            $activityQuery = LeadActivity::query();
        } else {
            // Usuarios regulares solo ven sus actividades
            $activityQuery = LeadActivity::where('user_id', $user->id);
        }

        $recentActivity = $activityQuery
            ->with(['lead', 'user'])
            ->where('activity_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('activity_date', 'desc')
            ->limit(10)
            ->get();

        // Conversiones por mes (últimos 6 meses)
        $conversionesPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $count = (clone $leadsQuery)
                ->whereHas('status', function($q) {
                    $q->where('name', 'Ganado');
                })
                ->whereYear('updated_at', $mes->year)
                ->whereMonth('updated_at', $mes->month)
                ->count();
            $conversionesPorMes[$mes->format('M Y')] = $count;
        }

        // Top vendedores (solo para usuarios con acceso completo)
        $topVendedores = [];
        if ($canViewAll) {
            $topVendedoresRaw = Lead::select('assigned_to', DB::raw('count(*) as total_leads'))
                ->whereHas('status', function($q) {
                    $q->where('name', 'Ganado');
                })
                ->whereMonth('updated_at', Carbon::now()->month)
                ->groupBy('assigned_to')
                ->orderBy('total_leads', 'desc')
                ->limit(5)
                ->get();

            // Cargar usuarios manualmente
            $topVendedores = $topVendedoresRaw->map(function($item) {
                $item->assignedTo = User::find($item->assigned_to);
                return $item;
            });
        }

        // Leads próximos a vencer
        $leadsProximos = (clone $leadsQuery)
            ->whereDate('expected_close_date', '<=', Carbon::now()->addDays(7))
            ->whereDate('expected_close_date', '>=', Carbon::now())
            ->whereHas('status', function($q) {
                $q->whereNotIn('name', ['Ganado', 'Perdido']);
            })
            ->with(['status', 'assignedTo'])
            ->orderBy('expected_close_date', 'asc')
            ->get();

        // Conversaciones con mensajes sin leer
        $conversationsQuery = CrmConversation::whereHas('messages', function($q) {
            $q->where('is_from_lead', true)->where('is_read', false);
        });

        if (!$canViewAll) {
            $conversationsQuery->where('vendedor_id', $user->vendedor_id);
        }

        $unreadConversations = $conversationsQuery->with(['lead', 'lastMessage'])->limit(5)->get();

        // Obtener todos los estados activos ordenados
        $allStatuses = LeadStatus::where('is_active', true)->orderBy('order')->get();

        // Obtener leads agrupados por estado para el Kanban board
        $leadsByStatusKanban = [];
        foreach ($allStatuses as $status) {
            $leads = (clone $leadsQuery)
                ->where('lead_status_id', $status->id)
                ->with(['assignedTo', 'vendedorSoftland', 'leadSource', 'tasks' => function($q) {
                    $q->orderBy('due_date', 'desc')->limit(1);
                }])
                ->withCount('notes') // Agregar conteo de notas
                ->orderBy('last_contact_at', 'desc') // Ordenar por último contacto (más recientes primero)
                ->get();

            $leadsByStatusKanban[$status->id] = [
                'status' => $status,
                'leads' => $leads
            ];
        }

        return view('crm.dashboard', compact(
            'stats',
            'leadsByStatus',
            'leadsByPriority',
            'recentActivity',
            'conversionesPorMes',
            'topVendedores',
            'leadsProximos',
            'unreadConversations',
            'canViewAll',
            'allStatuses',
            'leadsByStatusKanban'
        ));
    }

    public function updateLeadStatus(Request $request, $id)
    {
        $request->validate([
            'lead_status_id' => 'required|exists:lead_statuses,id'
        ]);

        $lead = Lead::findOrFail($id);

        // Verificar permisos
        $user = Auth::user();

        // Si el usuario puede editar todos los leads, permitir
        if ($user->can('edit.crm.all-leads')) {
            // Puede editar cualquier lead
        } elseif ($user->can('edit.crm.leads')) {
            // Puede editar solo sus propios leads
            if ($lead->vendedor_id !== $user->vendedor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes actualizar tus propios leads'
                ], 403);
            }
        } else {
            // No tiene permiso para editar leads
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar leads'
            ], 403);
        }

        $oldStatus = $lead->status;
        $lead->lead_status_id = $request->lead_status_id;
        $lead->save();

        // Registrar actividad
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => 'Estado Actualizado',
            'description' => "Estado cambiado de '{$oldStatus->name}' a '{$lead->status->name}'",
            'activity_date' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado del lead actualizado exitosamente',
            'lead' => $lead->load('status')
        ]);
    }
}
