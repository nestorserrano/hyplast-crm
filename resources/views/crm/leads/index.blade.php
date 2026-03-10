@extends('adminlte::page')

@section('title', 'Gestión de Leads')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-plus"></i> Gestión de Leads</h1>
        <a href="{{ route('crm.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </div>
@stop

@section('plugins.Datatables', true)
@section('plugins.HyplastNotifications', true)

@section('content')
<script>
    // Definir switchView INMEDIATAMENTE para que esté disponible desde los botones onclick
    window.switchView = function(viewType) {
        const tableView = document.getElementById('tableView');
        const kanbanView = document.getElementById('kanbanView');
        const btnTable = document.getElementById('btnTableView');
        const btnKanban = document.getElementById('btnKanbanView');
        const filtersRow = document.querySelector('.card-body .row.mb-3.align-items-center');

        if (viewType === 'table') {
            if (tableView) tableView.style.display = 'block';
            if (kanbanView) kanbanView.style.display = 'none';
            if (btnTable) btnTable.classList.add('active');
            if (btnKanban) btnKanban.classList.remove('active');
            if (filtersRow) filtersRow.style.display = 'flex'; // Mostrar filtros

            // Recargar y recalcular DataTable para reflejar cambios del Kanban
            if (window.leadsTable) {
                setTimeout(() => {
                    window.leadsTable.columns.adjust().draw(false);
                    window.leadsTable.ajax.reload(null, false); // false = mantener paginación
                }, 150);
            }
        } else if (viewType === 'kanban') {
            if (tableView) tableView.style.display = 'none';
            if (kanbanView) kanbanView.style.display = 'block';
            if (btnTable) btnTable.classList.remove('active');
            if (btnKanban) btnKanban.classList.add('active');
            if (filtersRow) filtersRow.style.display = 'none'; // Ocultar filtros

            // Recargar vista Kanban para reflejar cambios de la tabla
            reloadKanbanView();
        }
    };

    // Función para recargar la vista Kanban desde el servidor
    window.reloadKanbanView = function() {
        const kanbanBoard = document.querySelector('#kanbanView .kanban-board');
        if (!kanbanBoard) return;

        // Mostrar indicador de carga
        const originalContent = kanbanBoard.innerHTML;
        kanbanBoard.innerHTML = `
            <div class="text-center py-5 w-100">
                <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
                <p class="mt-3 text-muted">Actualizando vista Kanban...</p>
            </div>
        `;

        // Obtener datos actualizados del servidor
        fetch('/crm/leads/kanban-data', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                kanbanBoard.innerHTML = data.html;
                // Reinicializar drag & drop después de actualizar
                if (typeof initializeKanbanDragDrop === 'function') {
                    initializeKanbanDragDrop();
                }
            } else {
                throw new Error(data.message || 'Error al cargar datos');
            }
        })
        .catch(error => {
            console.error('Error recargando Kanban:', error);
            kanbanBoard.innerHTML = originalContent;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar la vista Kanban',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    };

    // Siempre cargar vista de tabla por defecto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            switchView('table');
        });
    } else {
        switchView('table');
    }
</script>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="card-title">Lista de Prospectos</h3>
                </div>
                <div class="col-md-6 text-right">
                    <!-- Botones de Vista -->
                    <div class="btn-group mr-2" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="btnTableView" onclick="switchView('table')">
                            <i class="fas fa-table"></i> Tabla
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnKanbanView" onclick="switchView('kanban')">
                            <i class="fas fa-columns"></i> Kanban
                        </button>
                    </div>
                    @can('crm.leads.create')
                    <button type="button" class="btn btn-success" onclick="openLeadModal('create')">
                        <i class="fas fa-plus"></i> Nuevo Lead
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros en una sola línea -->
            <div class="row mb-3 align-items-center">
                <div class="col-auto">
                    <label for="leads_length" class="mb-0">Mostrar</label>
                </div>
                <div class="col-auto">
                    <select class="form-control form-control-sm" id="leads_length">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="-1">Todos</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="mb-0">registros</label>
                </div>
                <div class="col-auto">
                    <label class="mb-0 font-weight-bold">Filtros:</label>
                </div>
                <div class="col-auto">
                    <input type="text" class="form-control form-control-sm" id="filter_company" placeholder="Buscar empresa..." style="min-width: 180px;">
                </div>
                <div class="col-auto">
                    <select class="form-control form-control-sm" id="filter_status">
                        <option value="">Todos los estados</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select class="form-control form-control-sm" id="filter_priority">
                        <option value="">Todas las prioridades</option>
                        <option value="1">Alta</option>
                        <option value="2">Media</option>
                        <option value="3">Baja</option>
                    </select>
                </div>
                @if(!Auth::user()->hasRole('Vendedor'))
                <div class="col-auto">
                    <select class="form-control form-control-sm" id="filter_vendedor">
                        <option value="">Todos los vendedores</option>
                        @foreach($vendedores as $vendedor)
                        <option value="{{ $vendedor->vendedor_id }}">{{ $vendedor->name }} - {{ $vendedor->vendedor_nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <!-- Vista Tabla DataTables -->
            <div id="tableView">
                <table id="leads-table" class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Empresa</th>
                            <th>Contacto</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Asignado a</th>
                            <th>Último Contacto</th>
                            <th>Estado</th>
                            <th>Canal Preferido</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Vista Kanban -->
            <div id="kanbanView" style="display: none;">
                <div class="kanban-board-wrapper" style="overflow-x: auto;">
                    <div class="kanban-board d-flex" style="min-width: max-content; gap: 15px;">
                        @include('crm.leads.partials.kanban-board')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir modal de formulario -->
@include('crm.leads.partials.modal-form')

<!-- Modal Timeline -->
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

<!-- Modal Notas -->
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

<!-- Modal Asignar Vendedor -->
<div class="modal fade" id="assignVendorModal" tabindex="-1" role="dialog" aria-labelledby="assignVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="assignVendorModalLabel">
                    <i class="fas fa-user-tag"></i> Asignar Vendedor
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeAssignVendorModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignVendorForm">
                    @csrf
                    <input type="hidden" id="assign_lead_id" name="lead_id">
                    <div class="form-group">
                        <label for="assign_vendedor_id">Seleccionar Vendedor <span class="text-danger">*</span></label>
                        <select class="form-control" id="assign_vendedor_id" name="vendedor_id" required>
                            <option value="">-- Seleccione un vendedor --</option>
                            @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->vendedor_id }}">{{ $vendedor->name }} - {{ $vendedor->vendedor_nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeAssignVendorModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="saveVendorAssignment()">
                    <i class="fas fa-save"></i> Asignar
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    /* Separación entre botones de acción */
    .d-flex.gap-1 > * {
        margin-right: 5px;
    }
    .d-flex.gap-1 > *:last-child {
        margin-right: 0;
    }
    /* Asegurar que los botones mantengan el mismo tamaño */
    #leads-table .btn-sm {
        white-space: nowrap;
    }
    /* Ocultar buscador y controles del DataTable */
    #leads-table_wrapper .dataTables_filter,
    #leads-table_wrapper .dataTables_length,
    #leads-table_wrapper .dt-buttons {
        display: none !important;
    }

    /* Estilos para botones de vista */
    .btn-group .btn-outline-secondary.active {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }

    /* Estilos Kanban */
    .kanban-board-wrapper {
        padding: 15px 0;
    }

    .kanban-column {
        flex-shrink: 0;
    }

    .kanban-card {
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }

    .kanban-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transform: translateY(-2px);
    }

    .kanban-column-body {
        background-color: #f8f9fa;
    }

    /* Scrollbar personalizado para columnas kanban */
    .kanban-column-body::-webkit-scrollbar {
        width: 6px;
    }

    .kanban-column-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .kanban-column-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .kanban-column-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Estilos para Drag & Drop */
    .sortable-ghost {
        opacity: 0.4;
        background: #f8f9fa;
    }

    .sortable-drag {
        opacity: 1;
        cursor: grabbing !important;
    }

    .kanban-card.dragging {
        opacity: 0.5;
    }
</style>
@stop

@section('js')
@include('scripts.datatables.datatables-leads')
@include('scripts.datatables.datatables-leads-modal')
@include('scripts.datatables.datatables-leads-assign')

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="{{ asset('js/lead-notes.js') }}"></script>
<script>
    // Variables globales
    const CSRF_TOKEN = '{{ csrf_token() }}';
    let quillEditor = null;
    let noteModal = null;
    let currentNoteId = null;

    // Inicializar Sortable en las columnas Kanban
    document.addEventListener('DOMContentLoaded', function() {
        initializeKanbanDragDrop();
    });

    function initializeKanbanDragDrop() {
        const kanbanColumns = document.querySelectorAll('.kanban-column-body');

        kanbanColumns.forEach(function(column) {
            new Sortable(column, {
                group: 'leads',
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                handle: '.kanban-card',
                onEnd: function(evt) {
                    const leadId = evt.item.dataset.leadId;
                    const newStatusId = evt.to.closest('.kanban-column').dataset.statusId;
                    const oldStatusId = evt.from.closest('.kanban-column').dataset.statusId;

                    // Solo actualizar si cambió de columna
                    if (newStatusId !== oldStatusId) {
                        updateLeadStatus(leadId, newStatusId, oldStatusId);
                    }
                }
            });
        });

        // Inicializar filtros de búsqueda
        initializeKanbanSearch();
    }

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

    function updateLeadStatus(leadId, newStatusId, oldStatusId) {
        // Mostrar indicador de carga
        const card = document.querySelector(`.kanban-card[data-lead-id="${leadId}"]`);
        if (card) {
            card.style.opacity = '0.5';
        }

        fetch(`/crm/leads/${leadId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                lead_status_id: newStatusId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Restaurar opacidad
                if (card) {
                    card.style.opacity = '1';
                }

                // Actualizar contadores
                updateColumnCounters();

                // Mostrar notificación de éxito
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Estado actualizado',
                        text: data.message || 'El lead ha sido movido exitosamente',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } else {
                throw new Error(data.message || 'Error al actualizar el estado');
            }
        })
        .catch(error => {
            console.error('Error:', error);

            // Revertir el movimiento
            const oldColumn = document.querySelector(`.kanban-column[data-status-id="${oldStatusId}"] .kanban-column-body`);
            if (card && oldColumn) {
                oldColumn.appendChild(card);
                card.style.opacity = '1';
            }

            // Mostrar error
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo actualizar el estado del lead',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar el estado del lead',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }

            updateColumnCounters();
        });
    }

    function updateColumnCounters() {
        document.querySelectorAll('.kanban-column').forEach(function(column) {
            const statusId = column.dataset.statusId;
            const count = column.querySelectorAll('.kanban-card').length;
            const badge = column.querySelector(`.kanban-count[data-status-id="${statusId}"]`);
            if (badge) {
                badge.textContent = count;
            }
        });
    }

    // Función para mostrar timeline - GLOBAL
    window.showLeadTimeline = function(leadId, leadName) {
        const notesManager = new LeadNotesManager(leadId, CSRF_TOKEN);
        document.getElementById('timelineLeadName').textContent = leadName;

        const modalElement = document.getElementById('timelineModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();

        notesManager.getTimeline()
            .then(timeline => {
                notesManager.renderTimeline(timeline, 'timelineContent');
            })
            .catch(error => {
                document.getElementById('timelineContent').innerHTML =
                    '<div class=\"alert alert-danger\">Error al cargar el timeline</div>';
            });
    };

    // Función para mostrar modal de notas - GLOBAL
    window.showAddNoteModal = function(leadId, leadName) {
        document.getElementById('noteLeadId').value = leadId;
        document.getElementById('noteLeadName').textContent = leadName;
        resetNoteForm();

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

        const modalElement = document.getElementById('addNoteModal');
        if (!noteModal) {
            noteModal = new bootstrap.Modal(modalElement);
        }
        noteModal.show();
        loadNotesList(leadId);
    };

    // Función para abrir modal de correo desde Kanban - GLOBAL
    window.openEmailFromKanban = function(leadId, leadEmail, leadName) {
        // Redirigir a la vista del lead con la pestaña de correos activa
        window.location.href = '/crm/leads/' + leadId + '?tab=emails&compose=true&to=' + encodeURIComponent(leadEmail);
    };

    function loadNotesList(leadId) {
        const notesListDiv = document.getElementById('notesList');
        notesListDiv.innerHTML = '<div class=\"text-center text-muted p-3\"><i class=\"fas fa-spinner fa-spin\"></i> Cargando notas...</div>';

        const notesManager = new LeadNotesManager(leadId, CSRF_TOKEN);
        notesManager.getNotes()
            .then(notes => {
                const notesCountElement = document.getElementById('notesCount');
                if (notesCountElement) {
                    notesCountElement.textContent = notes.length;
                }

                if (notes.length === 0) {
                    notesListDiv.innerHTML = '<div class=\"text-center text-muted p-3\"><i class=\"fas fa-inbox\"></i><br>No hay notas aún</div>';
                    updateLeadNoteBadge(leadId, 0);
                    return;
                }

                let html = '<div class=\"list-group\">';
                notes.forEach(note => {
                    const icon = getNoteIcon(note.type);
                    const color = getNoteColor(note.type);
                    const isPinned = note.is_pinned ? '<i class=\"fas fa-thumbtack text-warning\"></i> ' : '';
                    const date = new Date(note.created_at).toLocaleDateString('es-ES', {
                        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });

                    html += `
                        <a href=\"#\" class=\"list-group-item list-group-item-action\" onclick=\"loadNoteForEdit(${note.id}); return false;\">
                            <div class=\"d-flex w-100 justify-content-between\">
                                <h6 class=\"mb-1\">
                                    ${isPinned}
                                    <i class=\"${icon} text-${color}\"></i> ${note.type}
                                </h6>
                                <small>${date}</small>
                            </div>
                            <p class=\"mb-1 text-truncate\">${note.nota_sin_formato || stripHtml(note.content)}</p>
                            <small class=\"text-muted\">Por: ${note.user ? note.user.name : 'Usuario'}</small>
                        </a>
                    `;
                });
                html += '</div>';
                notesListDiv.innerHTML = html;
                updateLeadNoteBadge(leadId, notes.length);
            })
            .catch(error => {
                notesListDiv.innerHTML = '<div class=\"alert alert-danger\">Error al cargar notas</div>';
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

        if (quillEditor) {
            quillEditor.enable(false);
        }

        notesManager.getNote(noteId)
            .then(note => {
                currentNoteId = note.id;
                document.getElementById('noteId').value = note.id;
                document.getElementById('noteType').value = note.type;
                document.getElementById('notePinned').checked = note.is_pinned;

                if (quillEditor) {
                    quillEditor.enable(true);
                    quillEditor.root.innerHTML = note.content || '';
                }

                document.getElementById('noteModalTitle').textContent = 'Editar Nota';
                document.getElementById('saveBtnText').textContent = 'Actualizar';
                document.getElementById('cancelBtnText').textContent = 'Limpiar';
                document.getElementById('deleteNoteBtn').style.display = 'inline-block';
            })
            .catch(error => {
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

        document.getElementById('noteModalTitle').textContent = 'Agregar Nota';
        document.getElementById('saveBtnText').textContent = 'Guardar Nota';
        document.getElementById('cancelBtnText').textContent = 'Cancelar';
        document.getElementById('deleteNoteBtn').style.display = 'none';
    }

    function closeNoteModal() {
        if (noteModal) {
            noteModal.hide();
        }
        setTimeout(() => resetNoteForm(), 300);
    }

    function handleCancelNote() {
        const noteId = document.getElementById('noteId').value;
        if (noteId) {
            resetNoteForm();
        } else {
            closeNoteModal();
        }
    }

    function saveNote() {
        const leadId = document.getElementById('noteLeadId').value;
        const noteId = document.getElementById('noteId').value;
        const content = quillEditor.root.innerHTML.trim();
        const textContent = quillEditor.getText().trim();
        const type = document.getElementById('noteType').value;
        const isPinned = document.getElementById('notePinned').checked;

        if (!textContent || textContent.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor escribe el contenido de la nota'
            });
            return;
        }

        const notesManager = new LeadNotesManager(leadId, CSRF_TOKEN);
        const saveBtn = document.querySelector('#addNoteModal .btn-primary');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i> Guardando...';
        saveBtn.disabled = true;

        const savePromise = noteId
            ? notesManager.updateNote(noteId, content, textContent, type, isPinned)
            : notesManager.createNote(content, textContent, type, isPinned);

        savePromise
            .then(note => {
                loadNotesList(leadId);
                resetNoteForm();
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            })
            .catch(error => {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar la nota'
                });
            });
    }

    function deleteNote() {
        const noteId = document.getElementById('noteId').value;
        const leadId = document.getElementById('noteLeadId').value;

        if (!noteId) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'No hay nota seleccionada'
            });
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
            deleteBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i> Eliminando...';
            deleteBtn.disabled = true;

            notesManager.deleteNote(noteId)
                .then(() => {
                    loadNotesList(leadId);
                    resetNoteForm();
                    deleteBtn.innerHTML = originalText;
                    deleteBtn.disabled = false;
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Nota eliminada exitosamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .catch(error => {
                    deleteBtn.innerHTML = originalText;
                    deleteBtn.disabled = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar la nota'
                    });
                });
        });
    }

    function updateLeadNoteBadge(leadId, count) {
        const badge = document.querySelector(`.kanban-card[data-lead-id="${leadId}"] .badge-pill`);
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }
</script>
@stop
