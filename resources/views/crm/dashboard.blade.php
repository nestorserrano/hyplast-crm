@extends('adminlte::page')

@section('title', trans('crm.dashboard.title'))

@section('content_header')
    <h1><i class="fas fa-chart-pie"></i> {{ trans('crm.dashboard.title') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Estadísticas principales -->
    <div class="row">
        <div class="col-lg-4 col-6">
            <a href="{{ route('crm.leads.index') }}" style="text-decoration: none; color: inherit;">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_leads'] }}</h3>
                        <p>{{ trans('crm.dashboard.stats.total_leads') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['nuevos_leads'] }}</h3>
                    <p>{{ trans('crm.dashboard.stats.nuevos_leads') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['leads_ganados'] }}</h3>
                    <p>{{ trans('crm.dashboard.stats.ganados_mes') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Kanban de Leads por Estado -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-columns"></i> Pipeline de Leads</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary">Total: {{ collect($leadsByStatusKanban)->sum(fn($data) => $data['leads']->count()) }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="kanban-board-wrapper" style="overflow-x: auto;">
                        <div class="kanban-board d-flex p-3" style="min-width: max-content;">
                            @foreach($leadsByStatusKanban as $statusData)
                            <div class="kanban-column" data-status-id="{{ $statusData['status']->id }}" style="flex: 1; min-width: 350px; margin-right: 15px;">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Leads por Estado -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> {{ trans('crm.dashboard.charts.leads_por_estado') }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartLeadsByStatus" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Leads por Prioridad -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-circle"></i> {{ trans('crm.dashboard.charts.leads_por_prioridad') }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartLeadsByPriority" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Conversiones por Mes -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> {{ trans('crm.dashboard.charts.conversiones_mes') }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartConversionesMes" style="height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Leads Próximos a Vencer -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-exclamation"></i> {{ trans('crm.dashboard.sections.leads_proximos_vencer') }}</h3>
                    <span class="badge badge-warning float-right">{{ $leadsProximos->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>{{ trans('crm.dashboard.table.lead') }}</th>
                                <th>{{ trans('crm.dashboard.table.estado') }}</th>
                                <th>{{ trans('crm.dashboard.table.vence') }}</th>
                                <th>{{ trans('crm.dashboard.table.valor') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leadsProximos as $lead)
                            <tr>
                                <td>
                                    <a href="{{ route('crm.leads.show', $lead->id) }}">{{ $lead->name }}</a>
                                </td>
                                <td><span class="badge badge-{{ $lead->status->color }}">{{ $lead->status->name }}</span></td>
                                <td>{{ $lead->expected_close_date->diffForHumans() }}</td>
                                <td>RD${{ number_format($lead->estimated_value, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">{{ trans('crm.dashboard.messages.no_leads_proximos') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> {{ trans('crm.dashboard.sections.actividad_reciente') }}</h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentActivity->take(8) as $activity)
                        <a href="{{ route('crm.leads.show', $activity->lead_id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $activity->lead->name }}</h6>
                                <small>{{ $activity->activity_date->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small">
                                <span class="badge badge-secondary">{{ $activity->type }}</span>
                                {{ $activity->description }}
                            </p>
                            <small class="text-muted">{{ trans('crm.dashboard.messages.por_usuario', ['user' => $activity->user->name ?? 'Sistema']) }}</small>
                        </a>
                        @empty
                        <div class="list-group-item text-center text-muted">
                            {{ trans('crm.dashboard.messages.no_actividad_reciente') }}
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes Sin Leer -->
    @if($unreadConversations->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-envelope"></i> {{ trans('crm.dashboard.sections.mensajes_sin_leer') }}</h3>
                    <span class="badge badge-danger float-right">{{ $unreadConversations->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>{{ trans('crm.dashboard.table.lead') }}</th>
                                <th>{{ trans('crm.dashboard.table.canal') }}</th>
                                <th>{{ trans('crm.dashboard.table.ultimo_mensaje') }}</th>
                                <th>{{ trans('crm.dashboard.table.hace') }}</th>
                                <th>{{ trans('crm.dashboard.table.accion') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unreadConversations as $conv)
                            <tr>
                                <td>{{ $conv->lead->name }}</td>
                                <td>
                                    <i class="fab fa-whatsapp text-success"></i> {{ ucfirst($conv->channel) }}
                                </td>
                                <td>{{ Str::limit($conv->lastMessage->content ?? '', 50) }}</td>
                                <td>{{ $conv->last_message_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('crm.conversations.show', $conv->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-reply"></i> {{ trans('crm.dashboard.buttons.responder') }}
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Vendedores (solo para gerentes) -->
    @if($canViewAll && $topVendedores->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-medal"></i> {{ trans('crm.dashboard.sections.top_vendedores') }}</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('crm.dashboard.table.vendedor') }}</th>
                                <th>{{ trans('crm.dashboard.table.leads_ganados') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topVendedores as $index => $vendedor)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $vendedor->assignedTo->name }}</td>
                                <td><span class="badge badge-success">{{ $vendedor->total_leads }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal para Timeline del Lead -->
    <div class="modal fade" id="timelineModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-history"></i> Timeline de <span id="timelineLeadName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                    <div id="timelineContent">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="mt-2 text-muted">Cargando timeline...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar Nota -->
    <div class="modal fade" id="addNoteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-sticky-note"></i>
                        <span id="noteModalTitle">Agregar Nota</span> a <span id="noteLeadName"></span>
                    </h5>
                    <button type="button" class="close" onclick="closeNoteModal()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Columna izquierda: Formulario -->
                        <div class="col-md-6">
                            <form id="addNoteForm">
                                <input type="hidden" id="noteLeadId">
                                <input type="hidden" id="noteId">

                                <div class="form-group">
                                    <label>Tipo de Nota</label>
                                    <select class="form-control" id="noteType" name="type">
                                        <option value="note">Nota General</option>
                                        <option value="call">Llamada</option>
                                        <option value="email">Email</option>
                                        <option value="meeting">Reunión</option>
                                        <option value="whatsapp">WhatsApp</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Contenido <span class="text-danger">*</span></label>
                                    <div id="noteContent"></div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="notePinned" name="is_pinned">
                                        <label class="custom-control-label" for="notePinned">
                                            <i class="fas fa-thumbtack"></i> Destacar esta nota
                                        </label>
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-danger" id="deleteNoteBtn" style="display: none;" onclick="deleteNote()">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="cancelNoteBtn" onclick="handleCancelNote()">
                                        <i class="fas fa-times"></i> <span id="cancelBtnText">Cancelar</span>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="saveNote()">
                                        <i class="fas fa-save"></i> <span id="saveBtnText">Guardar Nota</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Columna derecha: Lista de notas -->
                        <div class="col-md-6 border-left">
                            <h6 class="mb-3">
                                <i class="fas fa-list"></i> Notas del Lead
                                <span class="badge badge-info" id="notesCount">0</span>
                            </h6>
                            <div id="notesList" style="max-height: 400px; overflow-y: auto;">
                                <div class="text-center text-muted p-3">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando notas...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="{{ asset('js/lead-notes.js') }}"></script>
<script>
// Variables y funciones globales
const CSRF_TOKEN = '{{ csrf_token() }}';

// Funciones globales para los modales
function showLeadTimeline(leadId, leadName) {
    const notesManager = new LeadNotesManager(leadId, CSRF_TOKEN);

    // Actualizar nombre del lead en el modal
    document.getElementById('timelineLeadName').textContent = leadName;

    // Mostrar modal usando Bootstrap nativo
    const modalElement = document.getElementById('timelineModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();

    // Cargar timeline
    notesManager.getTimeline()
        .then(timeline => {
            notesManager.renderTimeline(timeline, 'timelineContent');
        })
        .catch(error => {
            document.getElementById('timelineContent').innerHTML =
                '<div class="alert alert-danger">Error al cargar el timeline</div>';
        });
}

// Variable global para el editor Quill
let quillEditor = null;
let noteModal = null;
let currentNoteId = null;

function showAddNoteModal(leadId, leadName) {
    document.getElementById('noteLeadId').value = leadId;
    document.getElementById('noteLeadName').textContent = leadName;
    resetNoteForm();

    // Inicializar Quill Editor si no existe
    if (!quillEditor) {
        quillEditor = new Quill('#noteContent', {
            theme: 'snow',
            placeholder: 'Escribe tu nota aquí...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'header': [1, 2, 3, false] }],
                    ['link'],
                    ['clean']
                ]
            }
        });
    }

    // Mostrar modal usando Bootstrap nativo
    const modalElement = document.getElementById('addNoteModal');
    if (!noteModal) {
        noteModal = new bootstrap.Modal(modalElement);
    }
    noteModal.show();

    // Cargar lista de notas
    loadNotesList(leadId);
}

function loadNotesList(leadId) {
    const notesListDiv = document.getElementById('notesList');
    notesListDiv.innerHTML = '<div class="text-center text-muted p-3"><i class="fas fa-spinner fa-spin"></i> Cargando notas...</div>';

    const notesManager = new LeadNotesManager(leadId, CSRF_TOKEN);

    notesManager.getNotes()
        .then(notes => {
            // Actualizar contador en el modal si existe
            const notesCountElement = document.getElementById('notesCount');
            if (notesCountElement) {
                notesCountElement.textContent = notes.length;
            }

            if (notes.length === 0) {
                notesListDiv.innerHTML = '<div class="text-center text-muted p-3"><i class="fas fa-inbox"></i><br>No hay notas aún</div>';
                // Actualizar badge incluso si no hay notas
                updateLeadNoteBadge(leadId, 0);
                return;
            }

            let html = '<div class="list-group">';
            notes.forEach(note => {
                const icon = getNoteIcon(note.type);
                const color = getNoteColor(note.type);
                const isPinned = note.is_pinned ? '<i class="fas fa-thumbtack text-warning"></i> ' : '';
                const date = new Date(note.created_at).toLocaleDateString('es-ES', {
                    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                html += `
                    <a href="#" class="list-group-item list-group-item-action" onclick="loadNoteForEdit(${note.id}); return false;">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">
                                ${isPinned}
                                <i class="${icon} text-${color}"></i> ${note.type}
                            </h6>
                            <small>${date}</small>
                        </div>
                        <p class="mb-1 text-truncate">${note.nota_sin_formato || stripHtml(note.content)}</p>
                        <small class="text-muted">Por: ${note.user ? note.user.name : 'Usuario'}</small>
                    </a>
                `;
            });
            html += '</div>';

            notesListDiv.innerHTML = html;

            // Actualizar el badge en la tarjeta kanban
            updateLeadNoteBadge(leadId, notes.length);
        })
        .catch(error => {
            notesListDiv.innerHTML = '<div class="alert alert-danger">Error al cargar notas</div>';
        });
}

function getNoteIcon(type) {
    const icons = {
        'email': 'fas fa-envelope',
        'call': 'fas fa-phone',
        'meeting': 'fas fa-calendar-alt',
        'whatsapp': 'fab fa-whatsapp',
        'note': 'fas fa-sticky-note'
    };
    return icons[type] || 'fas fa-sticky-note';
}

function getNoteColor(type) {
    const colors = {
        'email': 'danger',
        'call': 'info',
        'meeting': 'warning',
        'whatsapp': 'success',
        'note': 'secondary'
    };
    return colors[type] || 'secondary';
}

function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}

function loadNoteForEdit(noteId) {
    const leadId = document.getElementById('noteLeadId').value;
    const notesManager = new LeadNotesManager(leadId, CSRF_TOKEN);

    // Deshabilitar editor mientras carga
    if (quillEditor) {
        quillEditor.enable(false);
    }

    notesManager.getNote(noteId)
        .then(note => {
            currentNoteId = note.id;
            document.getElementById('noteId').value = note.id;
            document.getElementById('noteType').value = note.type;
            document.getElementById('notePinned').checked = note.is_pinned;

            // Habilitar y cargar contenido en Quill
            if (quillEditor) {
                quillEditor.enable(true);
                quillEditor.root.innerHTML = note.content || '';
            }

            // Cambiar título y botones
            const modalTitle = document.getElementById('noteModalTitle');
            const saveBtnText = document.getElementById('saveBtnText');
            const cancelBtnText = document.getElementById('cancelBtnText');
            const deleteBtn = document.getElementById('deleteNoteBtn');

            if (modalTitle) modalTitle.textContent = 'Editar Nota';
            if (saveBtnText) saveBtnText.textContent = 'Actualizar';
            if (cancelBtnText) cancelBtnText.textContent = 'Limpiar';
            if (deleteBtn) deleteBtn.style.display = 'inline-block';

            showNotification('info', 'Nota cargada para edición');
        })
        .catch(error => {
            showNotification('error', 'Error al cargar la nota');
            if (quillEditor) {
                quillEditor.enable(true);
                quillEditor.setContents([]);
            }
        });
}

function resetNoteForm() {
    currentNoteId = null;
    document.getElementById('noteId').value = '';
    document.getElementById('addNoteForm').reset();

    if (quillEditor) {
        quillEditor.setContents([]);
    }

    // Restaurar título y botones
    const modalTitle = document.getElementById('noteModalTitle');
    const saveBtnText = document.getElementById('saveBtnText');
    const cancelBtnText = document.getElementById('cancelBtnText');
    const deleteBtn = document.getElementById('deleteNoteBtn');

    if (modalTitle) modalTitle.textContent = 'Agregar Nota';
    if (saveBtnText) saveBtnText.textContent = 'Guardar Nota';
    if (cancelBtnText) cancelBtnText.textContent = 'Cancelar';
    if (deleteBtn) deleteBtn.style.display = 'none';
}

function closeNoteModal() {
    if (noteModal) {
        noteModal.hide();
    }
    // Limpiar formulario al cerrar
    setTimeout(() => resetNoteForm(), 300);
}

function handleCancelNote() {
    const noteId = document.getElementById('noteId').value;

    if (noteId) {
        // Si está en modo edición, solo limpiar el formulario
        resetNoteForm();
    } else {
        // Si está en modo creación, cerrar el modal
        closeNoteModal();
    }
}

function saveNote() {
    const leadId = document.getElementById('noteLeadId').value;
    const noteId = document.getElementById('noteId').value;

    // Obtener contenido HTML del editor Quill
    const content = quillEditor.root.innerHTML.trim();

    // Obtener texto plano para validar que no esté vacío y guardarlo
    const textContent = quillEditor.getText().trim();

    const type = document.getElementById('noteType').value;
    const isPinned = document.getElementById('notePinned').checked;

    if (!textContent || textContent.length === 0) {
        showNotification('error', 'Por favor escribe el contenido de la nota');
        return;
    }

    const notesManager = new LeadNotesManager(leadId, CSRF_TOKEN);

    // Mostrar indicador de carga
    const saveBtn = document.querySelector('#addNoteModal .btn-primary');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    saveBtn.disabled = true;

    // Determinar si es creación o actualización
    const savePromise = noteId
        ? notesManager.updateNote(noteId, content, textContent, type, isPinned)
        : notesManager.createNote(content, textContent, type, isPinned);

    savePromise
        .then(note => {
            showNotification('success', noteId ? 'Nota actualizada exitosamente' : 'Nota guardada exitosamente');

            // Recargar lista de notas y actualizar contador
            loadNotesList(leadId);

            // Resetear formulario
            resetNoteForm();

            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error completo al guardar nota:', error);
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;

            let errorMsg = noteId ? 'Error al actualizar la nota' : 'Error al guardar la nota';
            if (error && error.message) {
                errorMsg += ': ' + error.message;
            }
            showNotification('error', errorMsg);
        });
}

function deleteNote() {
    const noteId = document.getElementById('noteId').value;
    const leadId = document.getElementById('noteLeadId').value;

    if (!noteId) {
        showNotification('error', 'No hay nota seleccionada');
        return;
    }

    Swal.fire({
        title: '¿Eliminar nota?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        const notesManager = new LeadNotesManager(leadId, CSRF_TOKEN);
        const deleteBtn = document.getElementById('deleteNoteBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
        deleteBtn.disabled = true;

        notesManager.deleteNote(noteId)
            .then(() => {
                showNotification('success', 'Nota eliminada exitosamente');
                loadNotesList(leadId); // Esto llamará a updateLeadNoteBadge automáticamente
                resetNoteForm();
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            })
            .catch(error => {
                showNotification('error', 'Error al eliminar la nota');
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            });
    });
}

function updateColumnCounters() {
    document.querySelectorAll('.kanban-column').forEach(function(column) {
        const count = column.querySelectorAll('.kanban-card').length;
        const badge = column.querySelector('.card-header .badge');
        if (badge) {
            badge.textContent = count;
        }
    });
}

function updateLeadNoteBadge(leadId, count) {
    // Buscar el botón de notas del lead específico
    const noteButtons = document.querySelectorAll(`button[onclick*="showAddNoteModal(${leadId}"]`);

    noteButtons.forEach(button => {
        // Buscar el badge dentro del botón
        let badge = button.querySelector('.badge');

        if (count > 0) {
            // Si hay notas, mostrar o actualizar el badge
            if (!badge) {
                // Crear el badge si no existe
                badge = document.createElement('span');
                badge.className = 'badge badge-pill badge-danger position-absolute';
                badge.style.top = '-5px';
                badge.style.right = '-8px';
                badge.style.fontSize = '9px';
                badge.style.padding = '2px 5px';
                button.appendChild(badge);
            }
            badge.textContent = count;
        } else {
            // Si no hay notas, eliminar el badge
            if (badge) {
                badge.remove();
            }
        }

        // Actualizar el title del botón
        button.title = `Notas (${count})`;
    });
}

function showNotification(type, message) {
    // Usar toastr si está disponible, sino usar SweetAlert
    if (typeof toastr !== 'undefined') {
        toastr[type](message);
    } else if (typeof Swal !== 'undefined') {
        const iconMap = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        Swal.fire({
            icon: iconMap[type] || 'info',
            title: type.charAt(0).toUpperCase() + type.slice(1),
            text: message,
            timer: 3000,
            toast: true,
            position: 'top-end',
            showConfirmButton: false
        });
    } else {
        console.log(`${type}: ${message}`);
    }
}

// Inicialización cuando el DOM está listo
$(document).ready(function() {
    // Gráfico de Leads por Estado
    const ctxStatus = document.getElementById('chartLeadsByStatus').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($leadsByStatus->keys()) !!},
            datasets: [{
                data: {!! json_encode($leadsByStatus->values()) !!},
                backgroundColor: [
                    '#17a2b8', // info
                    '#007bff', // primary
                    '#ffc107', // warning
                    '#6c757d', // secondary
                    '#6f42c1', // purple
                    '#28a745', // success
                    '#dc3545', // danger
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Gráfico de Leads por Prioridad
    const ctxPriority = document.getElementById('chartLeadsByPriority').getContext('2d');
    new Chart(ctxPriority, {
        type: 'pie',
        data: {
            labels: {!! json_encode($leadsByPriority->keys()) !!},
            datasets: [{
                data: {!! json_encode($leadsByPriority->values()) !!},
                backgroundColor: [
                    '#dc3545', // Alta - danger
                    '#ffc107', // Media - warning
                    '#17a2b8', // Baja - info
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Gráfico de Conversiones por Mes
    const ctxConversions = document.getElementById('chartConversionesMes').getContext('2d');
    new Chart(ctxConversions, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($conversionesPorMes)) !!},
            datasets: [{
                label: '{{ trans("crm.dashboard.charts.leads_ganados") }}',
                data: {!! json_encode(array_values($conversionesPorMes)) !!},
                backgroundColor: '#28a745',
                borderColor: '#28a745',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});

// Inicializar Kanban Board con Drag & Drop
document.addEventListener('DOMContentLoaded', function() {
    const kanbanColumns = document.querySelectorAll('.kanban-column-body');

    kanbanColumns.forEach(function(column) {
        new Sortable(column, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'kanban-ghost',
            dragClass: 'kanban-drag',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: function(evt) {
                const leadId = evt.item.dataset.leadId;
                const newStatusId = evt.to.closest('.kanban-column').dataset.statusId;
                const oldStatusId = evt.from.closest('.kanban-column').dataset.statusId;

                // Solo actualizar si cambió de columna
                if (newStatusId !== oldStatusId) {
                    updateLeadStatus(leadId, newStatusId, evt);
                }
            }
        });
    });

    // Inicializar filtros de búsqueda
    initializeKanbanSearch();
});

function initializeKanbanSearch() {
    const searchInputs = document.querySelectorAll('.kanban-search');

    searchInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const statusId = e.target.dataset.statusId;
            const column = document.querySelector(`.kanban-column[data-status-id="${statusId}"] .kanban-column-body`);
            const cards = column.querySelectorAll('.kanban-card');
            let visibleCount = 0;

            cards.forEach(function(card) {
                const company = card.dataset.company || '';

                if (company.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Actualizar contador visible
            const badge = document.querySelector(`.kanban-count[data-status-id="${statusId}"]`);
            if (badge) {
                const totalCount = cards.length;
                if (searchTerm) {
                    badge.textContent = `${visibleCount}/${totalCount}`;
                } else {
                    badge.textContent = totalCount;
                }
            }
        });
    });
}

function updateLeadStatus(leadId, newStatusId, evt) {
    // Mostrar loading
    const card = evt.item;
    const originalParent = evt.from;
    card.style.opacity = '0.5';

    fetch(`/crm/dashboard/leads/${leadId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            lead_status_id: newStatusId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar contadores
            updateColumnCounters();

            // Restaurar opacidad
            card.style.opacity = '1';

            // Mostrar mensaje de éxito
            showNotification('success', data.message || 'Estado actualizado correctamente');

            // Recargar página después de 1 segundo para actualizar estadísticas
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            // Revertir el movimiento
            originalParent.appendChild(card);
            card.style.opacity = '1';
            showNotification('error', data.message || 'Error al actualizar el estado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revertir el movimiento
        originalParent.appendChild(card);
        card.style.opacity = '1';
        showNotification('error', 'Error de conexión al actualizar el estado');
    });
}

</script>
@stop

@section('css')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
/* Estilos para Quill Editor */
#noteContent {
    height: 200px;
}
.ql-editor {
    min-height: 200px;
}

.list-group-item {
    border-left: 0;
    border-right: 0;
}
.list-group-item:first-child {
    border-top: 0;
}

/* Estilos del Kanban Board */
.kanban-board {
    display: flex;
    gap: 15px;
}

.kanban-column {
    flex: 0 0 auto;
}

.kanban-column .card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.kanban-column-body {
    background-color: #f8f9fa;
}

.kanban-card {
    transition: all 0.3s ease;
    border-left: 3px solid #007bff;
}

.kanban-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.kanban-ghost {
    opacity: 0.4;
    background-color: #e9ecef;
}

.kanban-drag {
    opacity: 0.8;
    transform: rotate(5deg);
}

.kanban-board-wrapper::-webkit-scrollbar {
    height: 8px;
}

.kanban-board-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.kanban-board-wrapper::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.kanban-board-wrapper::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.badge-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.kanban-card .card-body {
    border-radius: 0.25rem;
}

.kanban-card a {
    text-decoration: none;
}

.kanban-card a:hover {
    text-decoration: underline;
}

.timeline-item {
    padding-left: 50px;
    position: relative;
}

.timeline-icon {
    position: absolute;
    left: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
</style>
@stop
