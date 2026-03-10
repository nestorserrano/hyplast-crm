<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\LeadActivity;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ConversationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Verificar si existen leads y statuses configurados
        $hasLeads = Lead::exists();
        $hasStatuses = \App\Models\LeadStatus::exists();

        // Si no hay leads o statuses, mostrar vista informativa
        if (!$hasLeads || !$hasStatuses) {
            return view('crm.conversations.no-config', compact('hasLeads', 'hasStatuses'));
        }

        $conversationsQuery = CrmConversation::with(['lead.status', 'lastMessage'])
            ->orderBy('last_message_at', 'desc');

        // Usuarios con permiso para ver todas las conversaciones (gerentes, admins)
        if ($user->can('view.crm.all-conversations')) {
            // Ver todas las conversaciones
            $conversations = $conversationsQuery->get();
        } else {
            // Vendedores solo ven conversaciones de sus leads asignados
            if ($user->vendedor_id) {
                $conversations = $conversationsQuery
                    ->where('vendedor_id', $user->vendedor_id)
                    ->get();
            } else {
                // Si no tiene vendedor_id, no mostrar nada
                $conversations = collect();
            }
        }

        return view('crm.conversations.index', compact('conversations'));
    }

    public function show($id)
    {
        $conversation = CrmConversation::with(['lead', 'messages.user'])->findOrFail($id);

        $user = Auth::user();

        // Verificar acceso: puede ver todas las conversaciones O es su conversación asignada
        if (!$user->can('view.crm.all-conversations')) {
            if ($conversation->vendedor_id !== $user->vendedor_id) {
                abort(403, 'No tienes permiso para ver esta conversación');
            }
        }

        // Marcar mensajes como leídos
        $conversation->messages()
            ->where('is_from_lead', true)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        // Obtener conversaciones para el sidebar según permisos
        $conversationsQuery = CrmConversation::with(['lead.status', 'lastMessage'])
            ->orderBy('last_message_at', 'desc');

        if (!$user->can('view.crm.all-conversations')) {
            // Solo sus conversaciones
            $conversationsQuery->where('vendedor_id', $user->vendedor_id);
        }

        $conversations = $conversationsQuery->get();

        return view('crm.conversations.show', compact('conversation', 'conversations'));
    }

    public function getMessages($id)
    {
        $conversation = CrmConversation::with(['messages.user'])->findOrFail($id);

        $user = Auth::user();

        // Verificar acceso a la conversación
        if (!$user->can('view.crm.all-conversations')) {
            // Solo puede ver mensajes de conversaciones de sus leads
            if ($conversation->vendedor_id !== $user->vendedor_id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        }

        return response()->json($conversation->messages);
    }

    public function sendMessage(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|string|in:text,image,audio,video,document',
        ]);

        $conversation = CrmConversation::with('lead')->findOrFail($id);

        $user = Auth::user();

        // Verificar acceso para enviar mensajes
        if (!$user->can('view.crm.all-conversations')) {
            // Solo puede enviar a conversaciones de sus leads
            if ($conversation->vendedor_id !== $user->vendedor_id) {
                return response()->json(['error' => 'No autorizado para enviar mensajes a esta conversación'], 403);
            }
        }

        // Crear mensaje en la base de datos
        $message = CrmMessage::create([
            'conversation_id' => $id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'type' => $validated['type'] ?? 'text',
            'direction' => 'outbound',
            'status' => 'pending',
            'is_from_lead' => false,
            'is_read' => true,
        ]);

        // Enviar a WhatsApp si el canal es whatsapp
        if ($conversation->channel === 'whatsapp' && $conversation->lead->phone) {
            $whatsappService = new WhatsAppService();
            $result = $whatsappService->sendTextMessage(
                $conversation->lead->phone,
                $validated['content']
            );

            if ($result['success']) {
                // Actualizar mensaje con ID de WhatsApp
                $message->update([
                    'status' => 'sent',
                    'whatsapp_message_id' => $result['message_id']
                ]);
            } else {
                // Marcar como fallido
                $message->update(['status' => 'failed']);

                $errorCode = $result['error_code'] ?? null;
                $errorMessage = $result['error'] ?? 'Error desconocido';

                \Log::warning('Failed to send WhatsApp message', [
                    'conversation_id' => $id,
                'lead_phone' => $conversation->lead->phone,
                    'error_code' => $errorCode,
                    'error' => $errorMessage
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Error al enviar mensaje a WhatsApp: ' . $errorMessage,
                    'error_code' => $errorCode
                ], 500);
            }
        }

        // Actualizar última actividad de la conversación
        $conversation->update(['last_message_at' => now()]);

        // Registrar actividad
        LeadActivity::create([
            'lead_id' => $conversation->lead_id,
            'user_id' => Auth::id(),
            'type' => 'whatsapp_message_sent',
            'description' => 'Mensaje enviado vía ' . $conversation->channel . ': ' . substr($validated['content'], 0, 50),
            'activity_date' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->load('user')
        ]);
    }

    public function simulateLeadMessage(Request $request, $id)
    {
        // Solo para pruebas - simular mensaje del lead
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $conversation = CrmConversation::findOrFail($id);

        $message = CrmMessage::create([
            'conversation_id' => $id,
            'user_id' => null,
            'content' => $validated['content'],
            'type' => 'text',
            'is_from_lead' => true,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function create($leadId)
    {
        $lead = Lead::findOrFail($leadId);

        // Verificar si ya existe una conversación para este lead
        $existingConversation = CrmConversation::where('lead_id', $leadId)->first();

        if ($existingConversation) {
            return redirect()->route('crm.conversations.show', $existingConversation->id)
                ->with('info', 'Ya existe una conversación con este lead');
        }

        // Verificar permisos de acceso
        $user = auth()->user();
        if (!$user->can('view.crm.all-conversations')) {
            // Usuarios sin permiso completo solo pueden crear conversaciones de sus propios leads
            if ($lead->vendedor_id !== $user->vendedor_id) {
                abort(403, 'No tienes permiso para crear conversaciones de este lead');
            }
        }

        // Crear la conversación
        $conversation = CrmConversation::create([
            'lead_id' => $leadId,
            'vendedor_id' => auth()->id(),
            'channel' => 'whatsapp',
            'status' => 'active',
            'last_message_at' => now()
        ]);

        // Crear mensaje inicial del sistema
        CrmMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'content' => 'Conversación iniciada',
            'type' => 'text',
            'direction' => 'outbound',
            'status' => 'sent',
            'is_from_lead' => false,
            'is_read' => true,
        ]);

        // Registrar actividad
        LeadActivity::create([
            'lead_id' => $leadId,
            'user_id' => auth()->id(),
            'type' => 'conversation_started',
            'description' => 'Conversación iniciada por ' . auth()->user()->name,
            'activity_date' => now()
        ]);

        return redirect()->route('crm.conversations.show', $conversation->id)
            ->with('success', 'Conversación creada exitosamente');
    }
}
