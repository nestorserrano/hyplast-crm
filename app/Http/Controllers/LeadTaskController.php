<?php

namespace App\Http\Controllers;

use App\Models\LeadTask;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskStartNotification;

class LeadTaskController extends Controller
{
    public function index($leadId)
    {
        $tasks = LeadTask::where('lead_id', $leadId)
            ->with(['creator', 'assignedTo'])
            ->orderBy('position')
            ->get()
            ->groupBy('status');

        return response()->json([
            'success' => true,
            'tasks' => [
                'nuevo' => $tasks->get('nuevo', collect())->values(),
                'en_proceso' => $tasks->get('en_proceso', collect())->values(),
                'finalizado' => $tasks->get('finalizado', collect())->values(),
            ]
        ]);
    }

    public function store(Request $request, $leadId)
    {
        // Log temporal para debug
        \Log::info('LeadTaskController@store - Request data:', $request->all());

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_started' => 'boolean'
        ]);

        \Log::info('LeadTaskController@store - Validated data:', $validated);

        $validated['lead_id'] = $leadId;
        $validated['created_by'] = Auth::id();

        // Si la tarea se marca como iniciada, su status debe ser "en_proceso"
        $isStarted = isset($validated['is_started']) && $validated['is_started'] === true;
        $status = $isStarted ? 'en_proceso' : 'nuevo';

        $validated['status'] = $status;
        $validated['is_started'] = $isStarted; // Asegurar que esté en el array
        $validated['position'] = LeadTask::where('lead_id', $leadId)
            ->where('status', $status)
            ->max('position') + 1;

        $task = LeadTask::create($validated);

        \Log::info('LeadTaskController@store - Task created:', $task->toArray());

        $task->load(['creator', 'assignedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Tarea creada exitosamente',
            'task' => $task
        ], 201);
    }

    public function show($leadId, $taskId)
    {
        $task = LeadTask::where('lead_id', $leadId)
            ->with(['creator', 'assignedTo'])
            ->findOrFail($taskId);

        return response()->json([
            'success' => true,
            'task' => $task
        ]);
    }

    public function update(Request $request, $leadId, $taskId)
    {
        $task = LeadTask::where('lead_id', $leadId)->findOrFail($taskId);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'is_started' => 'boolean',
            'status' => 'sometimes|in:nuevo,en_proceso,finalizado'
        ]);

        // Si cambia is_started, actualizar status y position
        if (isset($validated['is_started'])) {
            $newIsStarted = $validated['is_started'];
            $oldIsStarted = $task->is_started;

            // Solo cambiar status si is_started realmente cambió
            if ($newIsStarted !== $oldIsStarted) {
                $newStatus = $newIsStarted ? 'en_proceso' : 'nuevo';

                // Recalcular posición en la nueva columna
                $validated['status'] = $newStatus;
                $validated['position'] = LeadTask::where('lead_id', $leadId)
                    ->where('status', $newStatus)
                    ->max('position') + 1;
            }
        }

        $task->update($validated);
        $task->load(['creator', 'assignedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Tarea actualizada exitosamente',
            'task' => $task
        ]);
    }

    public function destroy($leadId, $taskId)
    {
        $task = LeadTask::where('lead_id', $leadId)->findOrFail($taskId);
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tarea eliminada exitosamente'
        ]);
    }

    public function updateStatus(Request $request, $leadId, $taskId)
    {
        $task = LeadTask::where('lead_id', $leadId)->findOrFail($taskId);

        $validated = $request->validate([
            'status' => 'required|in:nuevo,en_proceso,finalizado'
        ]);

        $task->update(['status' => $validated['status']]);

        if ($validated['status'] === 'en_proceso') {
            $task->markAsStarted();
        }

        $task->load(['creator', 'assignedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'task' => $task
        ]);
    }

    public function toggleStarted(Request $request, $leadId, $taskId)
    {
        $task = LeadTask::where('lead_id', $leadId)->findOrFail($taskId);

        $newIsStarted = !$task->is_started;

        // Si se marca como iniciada, mover a "en_proceso"
        // Si se desmarca, volver a "nuevo"
        $newStatus = $newIsStarted ? 'en_proceso' : 'nuevo';

        // Recalcular posición en la nueva columna
        $newPosition = LeadTask::where('lead_id', $leadId)
            ->where('status', $newStatus)
            ->max('position') + 1;

        $task->update([
            'is_started' => $newIsStarted,
            'status' => $newStatus,
            'position' => $newPosition
        ]);

        $task->load(['creator', 'assignedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Estado de inicio actualizado',
            'task' => $task
        ]);
    }

    public function updatePosition(Request $request, $leadId, $taskId)
    {
        $validated = $request->validate([
            'status' => 'required|in:nuevo,en_proceso,finalizado',
            'position' => 'required|integer|min:0',
            'reset_started' => 'boolean'
        ]);

        $task = LeadTask::where('lead_id', $leadId)->findOrFail($taskId);

        $updateData = [
            'status' => $validated['status'],
            'position' => $validated['position']
        ];

        // Si vuelve a "nuevo", resetear is_started
        if ($validated['status'] === 'nuevo' || ($request->has('reset_started') && $request->reset_started)) {
            $updateData['is_started'] = false;
        }

        $task->update($updateData);

        if ($validated['status'] === 'en_proceso' && !$task->is_started) {
            $task->markAsStarted();
        }

        $task->load(['creator', 'assignedTo']);

        return response()->json([
            'success' => true,
            'task' => $task
        ]);
    }

    public function getGanttData($leadId)
    {
        $tasks = LeadTask::where('lead_id', $leadId)
            ->orderBy('start_date')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => (string) $task->id,
                    'name' => $task->title,
                    'start' => $task->start_date->format('Y-m-d H:i:s'),
                    'end' => $task->end_date->format('Y-m-d H:i:s'),
                    'progress' => $task->status === 'finalizado' ? 100 : ($task->status === 'en_proceso' ? 50 : 0),
                    'custom_class' => $task->status,
                    'dependencies' => ''
                ];
            });

        return response()->json([
            'success' => true,
            'tasks' => $tasks
        ]);
    }
}
