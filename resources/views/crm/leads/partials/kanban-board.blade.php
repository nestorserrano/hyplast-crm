@foreach($leadsByStatusKanban as $statusData)
<div class="kanban-column" data-status-id="{{ $statusData['status']->id }}" style="flex: 1; min-width: 350px;">
    <div class="card">
        <div class="card-header bg-{{ $statusData['status']->color }} text-white">
            <h5 class="card-title mb-0">
                {{ $statusData['status']->name }}
                <span class="badge badge-light text-dark float-right kanban-count" data-status-id="{{ $statusData['status']->id }}">{{ $statusData['leads']->count() }}</span>
            </h5>
        </div>
        <div class="card-body p-2" style="background-color: #f8f9fa;">
            <input type="text"
                   class="form-control form-control-sm kanban-search"
                   placeholder="Buscar empresa..."
                   data-status-id="{{ $statusData['status']->id }}"
                   style="margin-bottom: 8px; border-radius: 4px;">
        </div>
        <div class="card-body p-2 kanban-column-body" style="min-height: 200px; max-height: 600px; overflow-y: auto;">
            @forelse($statusData['leads'] as $lead)
            <div class="kanban-card card mb-2" data-lead-id="{{ $lead->id }}" data-company="{{ strtolower($lead->company ?? $lead->name) }}" style="cursor: move;">
                <div class="card-body p-2">
                    <!-- Empresa primero -->
                    @if($lead->company)
                    <h6 class="mb-1 font-weight-bold" style="color: #00a4bd;">
                        {{ Str::limit($lead->company, 30) }}
                    </h6>
                    @endif

                    <!-- Nombre del Contacto -->
                    <p class="mb-1 {{ $lead->company ? 'small text-muted' : 'font-weight-bold' }}" style="{{ $lead->company ? '' : 'color: #00a4bd;' }}">
                        <a href="{{ route('crm.leads.show', $lead->id) }}" style="color: inherit; text-decoration: none;" onclick="event.stopPropagation();">
                            {{ Str::limit($lead->name, 30) }}
                        </a>
                    </p>

                    <!-- Último Contacto -->
                    @if($lead->last_contact_at)
                    <p class="small text-muted mb-1">
                        Último contacto: {{ $lead->last_contact_at->format('d/m/Y') }}
                    </p>
                    @endif

                    <!-- Fuente y Última Tarea -->
                    @if($lead->leadSource)
                    <p class="small mb-1">
                        <span class="badge" style="background-color: #ff7a59; color: white; font-size: 9px; padding: 2px 6px;">{{ $lead->leadSource->name }}</span>
                    </p>
                    @endif

                    <!-- Última Tarea -->
                    @php
                        $lastTask = $lead->tasks->first();
                    @endphp
                    @if($lastTask)
                    <p class="small text-muted mb-2">
                        Tarea hace {{ $lastTask->due_date ? $lastTask->due_date->diffForHumans() : 'N/A' }}
                    </p>
                    @else
                    <p class="small text-muted mb-2">
                        Sin tareas
                    </p>
                    @endif

                    <!-- Iconos de Acción -->
                    <div class="d-flex align-items-center justify-content-end" style="border-top: 1px solid #e9ecef; padding-top: 8px; gap: 8px;">
                        <a href="{{ route('crm.leads.show', $lead->id) }}" class="btn btn-link btn-sm p-0 text-primary" title="Vista previa" onclick="event.stopPropagation();">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button type="button" class="btn btn-link btn-sm p-0 text-info" title="Timeline" onclick="event.stopPropagation(); showLeadTimeline({{ $lead->id }}, {{ json_encode($lead->name) }});">
                            <i class="fas fa-file-alt"></i>
                        </button>
                        <button type="button" class="btn btn-link btn-sm p-0 text-warning position-relative" title="Notas ({{ $lead->notes_count }})" onclick="event.stopPropagation(); showAddNoteModal({{ $lead->id }}, {{ json_encode($lead->name) }});">
                            <i class="fas fa-sticky-note"></i>
                            @if($lead->notes_count > 0)
                            <span class="badge badge-pill badge-danger position-absolute" style="top: -5px; right: -8px; font-size: 9px; padding: 2px 5px;">{{ $lead->notes_count }}</span>
                            @endif
                        </button>
                        <a href="{{ route('crm.leads.show', $lead->id) }}?tab=emails" class="btn btn-link btn-sm p-0 text-danger position-relative" title="Ver correos" onclick="event.stopPropagation();">
                            <i class="fas fa-envelope"></i>
                            @if($lead->emails()->where('is_read', false)->count() > 0)
                            <span class="badge badge-pill badge-primary position-absolute" style="top: -5px; right: -8px; font-size: 9px; padding: 2px 5px;">{{ $lead->emails()->where('is_read', false)->count() }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-3">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p class="small mb-0">Sin leads</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endforeach
