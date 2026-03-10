<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use App\Models\LeadActivity;
use App\Models\LeadTask;
use App\Helpers\ButtonHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class LeadController extends Controller
{
    public function index()
    {
        // Verificar si existen estados configurados
        $hasStatuses = LeadStatus::exists();

        // Si no hay estados, redirigir a vista informativa
        if (!$hasStatuses) {
            return redirect()->route('crm.lead-statuses.index')
                ->with('warning', 'Primero debe configurar los estados de leads antes de gestionar leads.');
        }

        $statuses = LeadStatus::where('is_active', true)->orderBy('order')->get();

        // Obtener todos los usuarios que tengan vendedor_id (sin importar tipo_usuario)
        $vendedores = User::whereNotNull('vendedor_id')
            ->with('vendedorSoftland')
            ->orderBy('name')
            ->get();

        // Obtener países desde producción
        $countries = \App\Models\CountryProduction::orderBy('name')->get();

        // Obtener fuentes de leads
        $sources = \App\Models\LeadSource::active()->ordered()->get();

        // Datos para vista Kanban
        $leadsByStatusKanban = [];
        $user = Auth::user();

        foreach ($statuses as $status) {
            $query = Lead::where('lead_status_id', $status->id)
                ->with(['tasks' => function($q) {
                    $q->latest();
                }, 'leadSource', 'assignedTo.vendedorSoftland'])
                ->withCount('notes');

            // Filtrar por vendedor si NO puede ver todos los leads
            if (!$user->can('view.crm.all-leads')) {
                $query->where('vendedor_id', $user->vendedor_id);
            }

            $leadsByStatusKanban[] = [
                'status' => $status,
                'leads' => $query->orderBy('last_contact_at', 'desc')->get()
            ];
        }

        return view('crm.leads.index', compact('statuses', 'vendedores', 'countries', 'sources', 'leadsByStatusKanban'));
    }

    public function kanbanData()
    {
        $statuses = LeadStatus::where('is_active', true)->orderBy('order')->get();
        $user = Auth::user();
        $leadsByStatusKanban = [];

        foreach ($statuses as $status) {
            $query = Lead::where('lead_status_id', $status->id)
                ->with(['tasks' => function($q) {
                    $q->latest();
                }, 'leadSource', 'assignedTo.vendedorSoftland'])
                ->withCount('notes');

            // Filtrar por vendedor si NO puede ver todos los leads
            if (!$user->can('view.crm.all-leads')) {
                $query->where('vendedor_id', $user->vendedor_id);
            }

            $leadsByStatusKanban[] = [
                'status' => $status,
                'leads' => $query->orderBy('last_contact_at', 'desc')->get()
            ];
        }

        $html = view('crm.leads.partials.kanban-board', compact('leadsByStatusKanban'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();
        $query = Lead::with(['status', 'assignedTo', 'assignedTo.vendedorSoftland', 'countryInfo', 'vendedorSoftland', 'leadSource']);

        // Filtrar leads basado en permisos
        if (!$user->can('view.crm.all-leads')) {
            // Vendedores solo ven sus leads asignados
            if ($user->vendedor_id) {
                $query->where('vendedor_id', $user->vendedor_id);
            } else {
                // Si no tiene vendedor_id asignado, no ver nada
                $query->whereRaw('1 = 0');
            }
        }
        // Usuarios con permiso view.crm.all-leads ven todos los leads

        // Filtros adicionales
        if ($request->filled('status_id')) {
            $query->where('lead_status_id', $request->status_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('company')) {
            $query->where('company', 'like', '%' . $request->company . '%');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('vendedor_nombre', function($lead) {
                if ($lead->vendedorSoftland) {
                    return $lead->vendedorSoftland->NOMBRE . ' <small class="text-muted">(' . $lead->vendedor_id . ')</small>';
                } elseif ($lead->assignedTo && $lead->assignedTo->vendedorSoftland) {
                    return $lead->assignedTo->vendedorSoftland->NOMBRE . ' <small class="text-muted">(' . $lead->assignedTo->vendedor_id . ')</small>';
                }
                return '<span class="text-muted">Sin asignar</span>';
            })
            ->addColumn('last_contact_formatted', function($lead) {
                if (!$lead->last_contact_at) {
                    return '<span class="text-muted">Sin contacto</span>';
                }
                $diffForHumans = $lead->last_contact_at->diffForHumans();
                $formatted = $lead->last_contact_at->format('d/m/Y H:i');
                return '<span title="' . $formatted . '">' . $diffForHumans . '</span>';
            })
            ->orderColumn('last_contact_formatted', function ($query, $order) {
                $query->orderBy('last_contact_at', $order);
            })
            ->addColumn('country_name', function($lead) {
                return $lead->countryInfo ? $lead->countryInfo->NOMBRE : ($lead->country ?: '<span class="text-muted">-</span>');
            })
            ->addColumn('status_badge', function($lead) {
                return '<span class="badge badge-' . $lead->status->color . '">' . $lead->status->name . '</span>';
            })
            ->addColumn('preferred_channel_badge', function($lead) {
                if (!$lead->preferred_channel) return '<span class="text-muted">-</span>';
                $icon = match($lead->preferred_channel) {
                    'whatsapp' => '<i class="fab fa-whatsapp text-success"></i>',
                    'email' => '<i class="fas fa-envelope text-info"></i>',
                    'phone' => '<i class="fas fa-phone text-primary"></i>',
                    'sms' => '<i class="fas fa-sms text-warning"></i>',
                    default => '<i class="fas fa-comment"></i>',
                };
                return $icon . ' ' . ucfirst($lead->preferred_channel);
            })
            ->addColumn('action', function($lead) {
                // Botón Ver (modal original)
                $btnShow = '<button type="button" class="btn btn-info btn-sm" style="min-width: 100px;" onclick="openLeadModal(\'view\', ' . $lead->id . ')"><i class="fas fa-eye"></i> Ver</button> ';

                // Botón para ir al detalle completo del lead
                $btnDetail = '<a href="' . route('crm.leads.show', $lead->id) . '" class="btn btn-secondary btn-sm" style="min-width: 100px;" title="Ver detalle completo"><i class="fas fa-file-alt"></i> Detalle</a> ';

                $btnAssign = '';
                if (Auth::user()->can('assign.crm.leads')) {
                    $btnAssign = '<button type="button" class="btn btn-warning btn-sm" style="min-width: 100px;" onclick="openAssignVendorModal(' . $lead->id . ')"><i class="fas fa-user-tag"></i> Asignar</button> ';
                }

                // Botón para ir a correos del lead (con parámetro tab)
                $btnEmail = '<a href="' . route('crm.leads.show', $lead->id) . '?tab=emails" class="btn btn-primary btn-sm" style="min-width: 100px;" title="Ver correos"><i class="fas fa-envelope"></i> Correos</a> ';

                return '<div class="d-flex gap-1">' . $btnShow . $btnDetail . $btnAssign . $btnEmail . '</div>';
            })
            ->rawColumns(['vendedor_nombre', 'country_name', 'last_contact_formatted', 'status_badge', 'preferred_channel_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $statuses = LeadStatus::where('is_active', true)->orderBy('order')->get();

        // Obtener vendedores (usuarios con tipo_usuario='vendedor' y que tengan vendedor_id)
        $vendedores = User::where('tipo_usuario', 'vendedor')
            ->whereNotNull('vendedor_id')
            ->with('vendedorSoftland')
            ->get();

        // Obtener países desde producción
        $countries = \App\Models\CountryProduction::orderBy('name')->get();

        return view('crm.leads.create', compact('statuses', 'vendedores', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:leads',
            'email' => 'nullable|email',  // Cambiado a nullable para permitir leads sin email (WhatsApp)
            'website' => 'nullable|url|max:255',
            'company' => 'nullable|string|max:255',  // Cambiado a nullable
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'notes' => 'nullable|string',
            'lead_status_id' => 'nullable|exists:lead_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'vendedor_id' => 'nullable|string|max:10',
            'source' => 'nullable|string',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'preferred_channel' => 'nullable|string|in:whatsapp,email,phone,sms',
            'expected_close_date' => 'nullable|date',
            'priority' => 'nullable|integer|between:1,3',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['last_contact_at'] = now();

        // Si se selecciona un vendedor en assigned_to, también guardar su vendedor_id
        if ($request->filled('assigned_to')) {
            $assignedUser = User::find($request->assigned_to);
            if ($assignedUser && $assignedUser->vendedor_id) {
                $validated['vendedor_id'] = $assignedUser->vendedor_id;
            }
        }

        $lead = Lead::create($validated);

        // Registrar actividad
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'type' => 'note',
            'description' => 'Lead creado',
            'activity_date' => now(),
        ]);

        // Si es una petición AJAX, responder con JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead creado exitosamente',
                'lead' => $lead
            ]);
        }

        return redirect()->route('crm.leads.index')->with('success', 'Lead creado exitosamente');
    }

    public function show($id)
    {
        $lead = Lead::with([
            'status',
            'assignedTo',
            'assignedTo.vendedorSoftland',
            'createdBy',
            'countryInfo',
            'countryProduction',
            'state',
            'city',
            'vendedorSoftland',
            'leadSource'
        ])->findOrFail($id);

        // Superadmins y admins tienen acceso total
        $user = Auth::user();
        if (!$user->hasRole('superadmin') && !$user->hasRole('admin')) {
            // Verificar permisos para usuarios normales
            if ($user->tipo_usuario === 'vendedor' && !$user->is_sales_manager) {
                // Vendedor regular solo puede ver sus leads
                if ($lead->vendedor_id !== $user->vendedor_id) {
                    abort(403, 'No tienes permiso para ver este lead');
                }
            }
        }

        // Cargar actividades y tareas manualmente para evitar errores
        $activities = $lead->activities()->with('user')->orderBy('activity_date', 'desc')->get();
        $tasks = $lead->tasks()->with('assignedTo')->orderBy('due_date')->get();
        $conversations = $lead->conversations()->with('messages')->get();

        // Si es una petición AJAX, responder solo con los datos del lead
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'lead' => $lead
            ]);
        }

        return view('crm.leads.show', compact('lead', 'activities', 'tasks', 'conversations'));
    }

    /**
     * Generar vista de impresión del lead
     */
    public function print($id)
    {
        $lead = Lead::with([
            'status',
            'assignedTo',
            'assignedTo.vendedorSoftland',
            'createdBy',
            'countryInfo',
            'countryProduction',
            'state',
            'city',
            'vendedorSoftland',
            'leadSource'
        ])->findOrFail($id);

        // Verificar permisos
        $user = Auth::user();
        if (!$user->hasRole('superadmin') && !$user->hasRole('admin')) {
            if ($user->tipo_usuario === 'vendedor' && !$user->is_sales_manager) {
                if ($lead->vendedor_id !== $user->vendedor_id) {
                    abort(403, 'No tienes permiso para imprimir este lead');
                }
            }
        }

        // Cargar relaciones que tienen conflicto de nombre con columnas
        $leadNotes = $lead->notes()->with('user')->orderBy('created_at', 'desc')->get();
        $leadTasks = $lead->tasks()->with('assignedTo')->orderBy('due_date', 'asc')->get();
        $leadActivities = $lead->activities()->with('user')->orderBy('created_at', 'desc')->get();

        return view('crm.leads.print', compact('lead', 'leadNotes', 'leadTasks', 'leadActivities'));
    }

    public function getLead($id)
    {
        $lead = Lead::with([
            'status',
            'assignedTo',
            'assignedTo.vendedorSoftland',
            'countryInfo',
            'countryProduction',
            'state',
            'city',
            'vendedorSoftland',
            'leadSource'
        ])->findOrFail($id);

        // Agregar información del vendedor si existe assigned_to
        if ($lead->assignedTo && $lead->assignedTo->vendedor_nombre) {
            $lead->vendedor_display_name = $lead->assignedTo->vendedor_nombre;
        }

        return response()->json([
            'success' => true,
            'lead' => $lead
        ]);
    }

    public function edit($id)
    {
        $lead = Lead::with(['assignedTo', 'assignedTo.vendedorSoftland'])->findOrFail($id);

        // Superadmins y admins tienen acceso total
        $user = Auth::user();
        if (!$user->hasRole('superadmin') && !$user->hasRole('admin')) {
            // Verificar permisos para usuarios normales
            if ($user->tipo_usuario === 'vendedor' && !$user->is_sales_manager) {
                if ($lead->vendedor_id !== $user->vendedor_id) {
                    abort(403, 'No tienes permiso para editar este lead');
                }
            }
        }

        $statuses = LeadStatus::where('is_active', true)->orderBy('order')->get();

        // Obtener vendedores (usuarios con tipo_usuario='vendedor' y que tengan vendedor_id)
        $vendedores = User::where('tipo_usuario', 'vendedor')
            ->whereNotNull('vendedor_id')
            ->with('vendedorSoftland')
            ->get();

        // Obtener países desde producción
        $countries = \App\Models\CountryProduction::orderBy('name')->get();

        return view('crm.leads.edit', compact('lead', 'statuses', 'vendedores', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        \Log::info('Update Lead - Request Data', [
            'lead_id' => $id,
            'all_data' => $request->all(),
            'assigned_to' => $request->assigned_to,
            'filled_assigned_to' => $request->filled('assigned_to')
        ]);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|unique:leads,phone,' . $id,
            'email' => 'nullable|email',
            'website' => 'nullable|url|max:255',
            'company' => 'nullable|string|max:255',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'notes' => 'nullable|string',
            'lead_status_id' => 'nullable|exists:lead_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'source' => 'nullable|string',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'preferred_channel' => 'nullable|string|in:whatsapp,email,phone,sms',
            'expected_close_date' => 'nullable|date',
            'priority' => 'nullable|integer|between:1,3',
        ]);

        \Log::info('Update Lead - Validated Data', ['validated' => $validated]);

        // Si se actualiza assigned_to, también actualizar vendedor_id
        if ($request->filled('assigned_to')) {
            $assignedUser = User::find($request->assigned_to);
            if ($assignedUser && $assignedUser->vendedor_id) {
                $validated['vendedor_id'] = $assignedUser->vendedor_id;
                \Log::info('Update Lead - Added vendedor_id', [
                    'vendedor_id' => $assignedUser->vendedor_id,
                    'user_name' => $assignedUser->name
                ]);
            }
        }

        // Registrar cambio de estado (solo si se envió y cambió)
        if (isset($validated['lead_status_id']) && $lead->lead_status_id != $validated['lead_status_id']) {
            $oldStatus = $lead->status ? $lead->status->name : 'Sin estado';
            $newStatus = LeadStatus::find($validated['lead_status_id']);
            if ($newStatus) {
                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => Auth::id(),
                    'type' => 'status_change',
                    'description' => 'Estado cambiado de ' . $oldStatus . ' a ' . $newStatus->name,
                    'activity_date' => now(),
                ]);
            }
        }

        $result = $lead->update($validated);

        \Log::info('Update Lead - Result', [
            'success' => $result,
            'lead_after_update' => $lead->fresh()->toArray()
        ]);

        // Si es una petición AJAX, responder con JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead actualizado exitosamente',
                'lead' => $lead
            ]);
        }

        return redirect()->route('crm.leads.show', $lead->id)->with('success', 'Lead actualizado exitosamente');
    }

    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);

        $user = Auth::user();
        // Superadmins y admins pueden eliminar cualquier lead
        if (!$user->hasRole('superadmin') && !$user->hasRole('admin')) {
            // Vendedores solo pueden eliminar sus propios leads
            if ($user->tipo_usuario === 'vendedor' && !$user->is_sales_manager) {
                if ($lead->vendedor_id !== $user->vendedor_id) {
                    return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
                }
            }
        }

        // Eliminar toda la información relacionada
        try {
            \DB::beginTransaction();

            // Eliminar actividades
            $lead->activities()->delete();

            // Eliminar tareas
            $lead->tasks()->delete();

            // Eliminar notas
            $lead->notes()->delete();

            // Eliminar conversaciones y sus mensajes
            foreach ($lead->conversations as $conversation) {
                $conversation->messages()->delete();
                $conversation->delete();
            }

            // Finalmente eliminar el lead
            $lead->delete();

            \DB::commit();

            return response()->json(['success' => true, 'message' => 'Lead y toda su información relacionada han sido eliminados exitosamente']);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al eliminar lead: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el lead'], 500);
        }
    }

    /**
     * Asignar lead a vendedor (solo para gerentes de ventas)
     */
    public function assignToVendedor(Request $request, $id)
    {
        $user = Auth::user();

        // Verificar que el usuario sea gerente de ventas o admin
        if (!$user->is_sales_manager && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para asignar leads'
            ], 403);
        }

        $validated = $request->validate([
            'vendedor_id' => 'required|string|exists:users,vendedor_id',
            'assigned_to' => 'required|integer|exists:users,id',
        ]);

        $lead = Lead::findOrFail($id);

        $oldVendedor = $lead->vendedor_id;
        $oldAssignedTo = $lead->assigned_to;

        $lead->vendedor_id = $validated['vendedor_id'];
        $lead->assigned_to = $validated['assigned_to'];
        $lead->save();

        // Registrar actividad de asignación
        $assignedUser = User::find($validated['assigned_to']);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'type' => 'assignment',
            'description' => 'Lead asignado a ' . $assignedUser->name . ' (' . $assignedUser->vendedor_nombre . ')',
            'activity_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead asignado exitosamente a ' . $assignedUser->name
        ]);
    }

    public function updateVendor(Request $request, $id)
    {
        $request->validate([
            'vendedor_id' => 'required|string|max:10'
        ]);

        $lead = Lead::findOrFail($id);

        // Buscar el usuario vendedor por su vendedor_id
        $assignedUser = User::where('vendedor_id', $request->vendedor_id)->first();

        if (!$assignedUser) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un usuario con ese código de vendedor'
            ], 404);
        }

        // Actualizar AMBOS campos: assigned_to (ID del usuario) y vendedor_id (código del vendedor)
        $lead->assigned_to = $assignedUser->id;
        $lead->vendedor_id = $request->vendedor_id;
        $lead->save();

        $vendedorInfo = $assignedUser->name;

        // Registrar actividad
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'type' => 'assignment',
            'description' => 'Vendedor actualizado a ' . $vendedorInfo,
            'activity_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vendedor asignado exitosamente a ' . $vendedorInfo
        ]);
    }

    public function updateStatus(Request $request, $id)
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
        $newStatus = LeadStatus::findOrFail($request->lead_status_id);

        $lead->lead_status_id = $request->lead_status_id;
        $lead->save();

        // Registrar actividad
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'type' => 'status_change',
            'description' => "Estado cambiado de '{$oldStatus->name}' a '{$newStatus->name}'",
            'activity_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Lead movido a {$newStatus->name}",
            'old_status' => $oldStatus->name,
            'new_status' => $newStatus->name
        ]);
    }
}
