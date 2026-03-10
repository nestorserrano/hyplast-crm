@extends('adminlte::page')

@section('title', 'Detalle Lead')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            {{ $lead->company ?: $lead->name }}
            @if($lead->company)
                <small class="text-muted" style="font-size: 0.6em;">{{ $lead->name }}</small>
            @endif
        </h1>
        <div>
            <a href="{{ route('crm.dashboard') }}" class="btn btn-secondary mr-1">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="{{ route('crm.leads.index') }}" class="btn btn-secondary">
                <i class="fas fa-list"></i> Lista de Leads
            </a>
        </div>
    </div>
@stop

@section('content')
@php
    $activeTab = request()->get('tab', 'info');
    $currentUser = Auth::user();
    $microsoftToken = $currentUser->microsoftToken;
    $isMicrosoftConnected = $microsoftToken && !$microsoftToken->isExpired();
@endphp
<div class="row">
    <!-- Información Principal -->
    <div class="col-md-8">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="lead-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'info' ? 'active' : '' }}" id="info-tab" data-toggle="tab" href="#info" role="tab">
                            <i class="fas fa-info-circle"></i> Información
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'activities' ? 'active' : '' }}" id="activities-tab" data-toggle="tab" href="#activities" role="tab">
                            <i class="fas fa-history"></i> Actividades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'tasks' ? 'active' : '' }}" id="tasks-tab" data-toggle="tab" href="#tasks" role="tab">
                            <i class="fas fa-tasks"></i> Tareas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'notes' ? 'active' : '' }}" id="notes-tab" data-toggle="tab" href="#notes" role="tab">
                            <i class="fas fa-sticky-note"></i> Notas
                            @if($lead->notes()->count() > 0)
                            <span class="badge badge-danger">{{ $lead->notes()->count() }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'emails' ? 'active' : '' }}" id="emails-tab" data-toggle="tab" href="#emails" role="tab">
                            <i class="fas fa-envelope"></i> Correos
                            @if($isMicrosoftConnected && $lead->emails()->where('is_read', false)->count() > 0)
                            <span class="badge badge-primary">{{ $lead->emails()->where('is_read', false)->count() }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="lead-tabContent">
                    <!-- Tab Información -->
                    <div class="tab-pane fade {{ $activeTab == 'info' ? 'show active' : '' }}" id="info" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Datos de Contacto</h5>
                                <dl class="row">
                                    @if($lead->company)
                                    <dt class="col-sm-4">Empresa:</dt>
                                    <dd class="col-sm-8">{{ $lead->company }}</dd>
                                    @endif

                                    <dt class="col-sm-4">Nombre:</dt>
                                    <dd class="col-sm-8">{{ $lead->name }}</dd>

                                    <dt class="col-sm-4">Teléfono:</dt>
                                    <dd class="col-sm-8">
                                        <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                                    </dd>

                                    @if($lead->email)
                                    <dt class="col-sm-4">Email:</dt>
                                    <dd class="col-sm-8">
                                        <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                                    </dd>
                                    @endif

                                    <dt class="col-sm-4">Sitio Web:</dt>
                                    <dd class="col-sm-8">
                                        @if($lead->website)
                                        <a href="{{ $lead->website }}" target="_blank" rel="noopener noreferrer">
                                            {{ $lead->website }} <i class="fas fa-external-link-alt fa-xs"></i>
                                        </a>
                                        @else
                                        <span class="text-muted">No especificado</span>
                                        @endif
                                    </dd>

                                    <dt class="col-sm-4">País:</dt>
                                    <dd class="col-sm-8">
                                        @if($lead->countryProduction)
                                        {{ $lead->countryProduction->name }}
                                        @else
                                        <span class="text-muted">No especificado</span>
                                        @endif
                                    </dd>

                                    <dt class="col-sm-4">Estado:</dt>
                                    <dd class="col-sm-8">
                                        @if($lead->state)
                                        {{ $lead->state->name }}
                                        @else
                                        <span class="text-muted">No especificado</span>
                                        @endif
                                    </dd>

                                    <dt class="col-sm-4">Ciudad:</dt>
                                    <dd class="col-sm-8">
                                        @if($lead->city)
                                        {{ $lead->city->name }}
                                        @else
                                        <span class="text-muted">No especificado</span>
                                        @endif
                                    </dd>

                                    @if($lead->preferred_channel)
                                    <dt class="col-sm-4">Canal Preferido:</dt>
                                    <dd class="col-sm-8">
                                        @php
                                            $channelIcons = [
                                                'whatsapp' => '<i class="fab fa-whatsapp text-success"></i> WhatsApp',
                                                'email' => '<i class="fas fa-envelope text-info"></i> Email',
                                                'phone' => '<i class="fas fa-phone text-primary"></i> Teléfono',
                                                'sms' => '<i class="fas fa-sms text-warning"></i> SMS'
                                            ];
                                        @endphp
                                        {!! $channelIcons[$lead->preferred_channel] ?? ucfirst($lead->preferred_channel) !!}
                                    </dd>
                                    @endif
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h5>Información de Venta</h5>
                                <dl class="row">
                                    <dt class="col-sm-5">Estado:</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge badge-{{ $lead->status->color }}">{{ $lead->status->name }}</span>
                                    </dd>

                                    <dt class="col-sm-5">Prioridad:</dt>
                                    <dd class="col-sm-7">
                                        @php
                                            $priorityBadges = [
                                                1 => '<span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Alta</span>',
                                                2 => '<span class="badge badge-warning"><i class="fas fa-minus-circle"></i> Media</span>',
                                                3 => '<span class="badge badge-info"><i class="fas fa-arrow-down"></i> Baja</span>'
                                            ];
                                        @endphp
                                        {!! $priorityBadges[$lead->priority] ?? '<span class="badge badge-secondary">Sin prioridad</span>' !!}
                                    </dd>

                                    <dt class="col-sm-5">Asignado a:</dt>
                                    <dd class="col-sm-7">
                                        @if($lead->assignedTo)
                                            {{ $lead->assignedTo->vendedor_id }} - {{ $lead->assignedTo->vendedorSoftland ? $lead->assignedTo->vendedorSoftland->NOMBRE : $lead->assignedTo->vendedor_nombre }}
                                        @else
                                            <span class="text-muted">Sin asignar</span>
                                        @endif
                                    </dd>

                                    @if($lead->leadSource)
                                    <dt class="col-sm-5">Fuente:</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge badge-{{ $lead->leadSource->color }}">{{ $lead->leadSource->name }}</span>
                                    </dd>
                                    @elseif($lead->source)
                                    <dt class="col-sm-5">Fuente:</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge badge-secondary">{{ ucfirst($lead->source) }}</span>
                                    </dd>
                                    @endif

                                    @if($lead->expected_close_date)
                                    <dt class="col-sm-5">Fecha Estimada:</dt>
                                    <dd class="col-sm-7">{{ $lead->expected_close_date->format('d/m/Y') }}</dd>
                                    @endif

                                    @if($lead->last_contact_at)
                                    <dt class="col-sm-5">Último Contacto:</dt>
                                    <dd class="col-sm-7">{{ $lead->last_contact_at->diffForHumans() }}</dd>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        @if($lead->notes)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Notas</h5>
                                <div class="callout callout-info">
                                    {{ $lead->notes }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Conversaciones</h5>
                                @if($conversations->count() > 0)
                                    @foreach($conversations as $conversation)
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <strong>Última actualización:</strong>
                                                        {{ $conversation->updated_at->diffForHumans() }}
                                                    </div>
                                                    <div>
                                                        @if($conversation->unreadCount() > 0)
                                                            <span class="badge badge-danger">
                                                                {{ $conversation->unreadCount() }} sin leer
                                                            </span>
                                                        @endif
                                                        <a href="{{ route('crm.conversations.show', $conversation->id) }}"
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-comment"></i> Ver Chat
                                                        </a>
                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}"
                                                           target="_blank"
                                                           class="btn btn-sm btn-success"
                                                           title="Abrir en WhatsApp">
                                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                                        </a>
                                                    </div>
                                                </div>
                                                @if($conversation->lastMessage)
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            Último mensaje:
                                                            {{ Str::limit($conversation->lastMessage->content, 100) }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460;">
                                        No hay conversaciones registradas.
                                        <a href="{{ route('crm.conversations.create', $lead->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-comments"></i> Iniciar conversación
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tab Actividades -->
                    <div class="tab-pane fade {{ $activeTab == 'activities' ? 'show active' : '' }}" id="activities" role="tabpanel">
                        <div class="timeline">
                            @forelse($activities as $activity)
                                <div>
                                    <i class="fas
                                        @if($activity->type == 'call') fa-phone
                                        @elseif($activity->type == 'meeting') fa-calendar
                                        @elseif($activity->type == 'email') fa-envelope
                                        @elseif($activity->type == 'note') fa-sticky-note
                                        @elseif($activity->type == 'status_change') fa-exchange-alt
                                        @else fa-circle
                                        @endif
                                        bg-blue
                                    "></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> {{ $activity->created_at->diffForHumans() }}
                                        </span>
                                        <h3 class="timeline-header">
                                            <strong>{{ $activity->user->name ?? 'Usuario desconocido' }}</strong> -
                                            {{ ucfirst($activity->type) }}
                                        </h3>
                                        <div class="timeline-body">
                                            {{ $activity->description }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-info">
                                    No hay actividades registradas.
                                </div>
                            @endforelse
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Tareas -->
                    <div class="tab-pane fade {{ $activeTab == 'tasks' ? 'show active' : '' }}" id="tasks" role="tabpanel">
                        <!-- Toggle Vista y Botón Agregar -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary active" id="kanbanViewBtn">
                                    <input type="radio" name="taskView" value="kanban" checked>
                                    <i class="fas fa-columns"></i> Kanban
                                </label>
                                <label class="btn btn-outline-primary" id="calendarViewBtn">
                                    <input type="radio" name="taskView" value="calendar">
                                    <i class="fas fa-calendar-alt"></i> Calendario
                                </label>
                            </div>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#taskModal" onclick="resetTaskModal()">
                                <i class="fas fa-plus"></i> Nueva Tarea
                            </button>
                        </div>

                        <!-- Vista Kanban -->
                        <div id="kanbanView" class="kanban-board">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="kanban-column" data-status="nuevo">
                                        <div class="kanban-column-header bg-info">
                                            <h5><i class="fas fa-inbox"></i> NUEVO</h5>
                                            <span class="badge badge-light" id="badge-nuevo">0</span>
                                        </div>
                                        <div class="kanban-column-body" id="column-nuevo" data-status="nuevo">
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x"></i>
                                                <p>No hay tareas nuevas</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="kanban-column" data-status="en_proceso">
                                        <div class="kanban-column-header bg-warning">
                                            <h5><i class="fas fa-spinner"></i> EN PROCESO</h5>
                                            <span class="badge badge-light" id="badge-en_proceso">0</span>
                                        </div>
                                        <div class="kanban-column-body" id="column-en_proceso" data-status="en_proceso">
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-spinner fa-2x"></i>
                                                <p>No hay tareas en proceso</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="kanban-column" data-status="finalizado">
                                        <div class="kanban-column-header bg-success">
                                            <h5><i class="fas fa-check-circle"></i> FINALIZADO</h5>
                                            <span class="badge badge-light" id="badge-finalizado">0</span>
                                        </div>
                                        <div class="kanban-column-body" id="column-finalizado" data-status="finalizado">
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-check-circle fa-2x"></i>
                                                <p>No hay tareas finalizadas</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vista Calendario -->
                        <div id="calendarView" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <div id="taskCalendar"></div>
                                    <div id="calendarEmpty" class="text-center text-muted py-5" style="display:none;">
                                        <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                        <p>No hay tareas para mostrar en el calendario</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Notas -->
                    <div class="tab-pane fade {{ $activeTab == 'notes' ? 'show active' : '' }}" id="notes" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" onclick="showNoteForm()">
                                    <i class="fas fa-plus"></i> Agregar Nota
                                </button>
                            </div>
                        </div>

                        <!-- Formulario de Nota -->
                        <div id="noteFormContainer" style="display: none;">
                            <div class="card" id="noteFormCard">
                                <div class="card-header" id="noteFormHeader">
                                    <h5 class="mb-0" id="noteFormHeaderTitle">
                                        <i class="fas fa-sticky-note"></i>
                                        <span id="noteFormTitle">Nueva Nota</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="leadNoteForm">
                                        <input type="hidden" id="leadNoteId">

                                        <div class="form-group">
                                            <label>Tipo de Nota</label>
                                            <select class="form-control" id="leadNoteType" name="type">
                                                <option value="note">Nota General</option>
                                                <option value="call">Llamada</option>
                                                <option value="email">Email</option>
                                                <option value="meeting">Reunión</option>
                                                <option value="whatsapp">WhatsApp</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Contenido <span class="text-danger">*</span></label>
                                            <div id="leadNoteContent"></div>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="leadNotePinned">
                                                <label class="custom-control-label" for="leadNotePinned">
                                                    <i class="fas fa-thumbtack"></i> Destacar esta nota
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <button type="button" class="btn btn-danger mr-2" id="deleteLeadNoteBtn" style="display: none;" onclick="deleteLeadNote()">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                            <button type="button" class="btn btn-secondary mr-2" onclick="cancelNoteForm()">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                            <button type="button" class="btn btn-primary" onclick="saveLeadNote()">
                                                <i class="fas fa-save"></i> <span id="saveLeadNoteText">Guardar</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Notas -->
                        <div id="leadNotesList">
                            <div class="text-center text-muted p-3">
                                <i class="fas fa-spinner fa-spin"></i> Cargando notas...
                            </div>
                        </div>
                    </div>

                    <!-- Tab Correos -->
                    @include('crm.leads.partials.emails-tab')
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Derecho -->
    <div class="col-md-4">
        <!-- Estado y Acciones -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estado del Lead</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge badge-{{ $lead->status->color ?? 'secondary' }} p-3" style="font-size: 1.2rem;">
                        {{ $lead->status->name ?? 'Sin estado' }}
                    </span>
                </div>
                <div class="text-center mb-3">
                    <span class="badge {{ $lead->priority_badge }} p-2" style="font-size: 1rem;">
                        Prioridad: {{ $lead->priority_text }}
                    </span>
                </div>

                <div class="btn-group d-flex" role="group">
                    @can('crm.leads.edit')
                    <a href="{{ route('crm.leads.edit', $lead->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    @endcan

                    @if($lead->conversations->first())
                    <a href="{{ route('crm.conversations.show', $lead->conversations->first()->id) }}"
                       class="btn btn-success">
                        <i class="fab fa-whatsapp"></i> Chat
                    </a>
                    @endif
                </div>

                @if(Auth::user()->is_sales_manager || Auth::user()->hasRole('admin'))
                <div class="row mt-3">
                    <div class="col-6">
                        <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#assignModal">
                            <i class="fas fa-user-tag"></i> Asignar Vendedor
                        </button>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('crm.leads.print', $lead->id) }}" target="_blank" class="btn btn-secondary btn-block">
                            <i class="fas fa-print"></i> Imprimir Lead
                        </a>
                    </div>
                </div>
                @endif

                @can('crm.leads.delete')
                <button type="button" class="btn btn-danger btn-block mt-3" id="btnDeleteLead" data-lead-id="{{ $lead->id }}">
                    <i class="fas fa-trash"></i> Eliminar Lead
                </button>
                </form>
                @endcan
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información Adicional</h3>
            </div>
            <div class="card-body">
                @if($lead->assignedTo)
                <p><strong>Asignado a:</strong><br>
                    {{ $lead->assignedTo->name ?? 'No asignado' }}
                    @if($lead->assignedTo && $lead->assignedTo->vendedorSoftland)
                        <br><small class="text-muted">{{ $lead->assignedTo->vendedorSoftland->NOMBRE }}</small>
                    @endif
                </p>
                @endif

                @if($lead->source)
                <p><strong>Fuente:</strong><br>{{ ucfirst($lead->source) }}</p>
                @endif

                <p><strong>Creado por:</strong><br>{{ $lead->createdBy->name ?? 'Desconocido' }}</p>
                <p><strong>Fecha de creación:</strong><br>{{ $lead->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Última modificación:</strong><br>{{ $lead->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Asignar Vendedor -->
@if(Auth::user()->is_sales_manager || Auth::user()->hasRole('admin'))
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="assignModalLabel">
                    <i class="fas fa-user-tag"></i> Asignar Prospecto a Usuario
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="vendedor_select">Seleccionar Vendedor</label>
                        <select class="form-control" id="vendedor_select" name="vendedor_id" required>
                            <option value="">-- Seleccione un vendedor --</option>
                            @foreach(App\Models\User::where('tipo_usuario', 'vendedor')->whereNotNull('vendedor_id')->get() as $vendedor)
                            <option value="{{ $vendedor->vendedor_id }}" data-user-id="{{ $vendedor->id }}"
                                {{ $lead->vendedor_id == $vendedor->vendedor_id ? 'selected' : '' }}>
                                {{ $vendedor->vendedor_id }} - {{ $vendedor->vendedor_nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" id="assigned_to" name="assigned_to">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Crear/Editar Tarea -->
<div class="modal fade" id="taskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="taskModalTitle">Nueva Tarea</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="taskForm">
                    <input type="hidden" id="taskId">

                    <div class="form-group">
                        <label>Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="taskTitle" required>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" id="taskDescription" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha y Hora Inicio <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="taskStartDate" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha y Hora Fin <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="taskEndDate" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="taskIsStarted">
                            <label class="custom-control-label" for="taskIsStarted">
                                <i class="fas fa-play-circle"></i> Marcar como iniciada
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="deleteTaskBtn" style="display:none;" onclick="deleteTask()">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
                <button type="button" class="btn btn-primary" onclick="saveTask()">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Composición de Correo -->
@include('crm.leads.modals.compose-email')

<!-- Modal de Detalle de Correo -->
<div class="modal fade" id="emailDetailModal" tabindex="-1" role="dialog" aria-labelledby="emailDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailDetailModalLabel">
                    <i class="fas fa-envelope"></i> Detalle del Correo
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="emailDetailContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando correo...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    .timeline {
        position: relative;
        margin: 0 0 30px 0;
        padding: 0;
        list-style: none;
    }
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #ddd;
        left: 31px;
        margin: 0;
        border-radius: 2px;
    }
    .timeline > div {
        position: relative;
        margin-right: 0;
        margin-bottom: 15px;
    }
    .timeline > div > .fas {
        width: 30px;
        height: 30px;
        font-size: 15px;
        line-height: 30px;
        position: absolute;
        color: #fff;
        background: #999;
        border-radius: 50%;
        text-align: center;
        left: 18px;
        top: 0;
    }
    .timeline > div > .timeline-item {
        margin-left: 60px;
        margin-right: 15px;
        padding: 0;
        background: #fff;
        color: #444;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    .timeline > div > .timeline-item > .time {
        color: #999;
        float: right;
        padding: 10px;
        font-size: 12px;
    }
    .timeline > div > .timeline-item > .timeline-header {
        margin: 0;
        color: #555;
        border-bottom: 1px solid #f4f4f4;
        padding: 10px;
        font-size: 16px;
        line-height: 1.1;
    }
    .timeline > div > .timeline-item > .timeline-body {
        padding: 10px;
    }

    /* Estilos para formulario de notas en modo edición */
    #noteFormCard.editing {
        border: 2px solid #007bff !important;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
    }

    #noteFormHeader.editing {
        background-color: #007bff;
        color: white;
    }

    #noteFormHeaderTitle.editing {
        color: white !important;
    }

    /* Altura del editor Quill */
    #leadNoteContent .ql-editor {
        min-height: 250px;
        max-height: 400px;
    }

    /* Estilos Kanban Board */
    .kanban-board {
        margin: 0 -5px;
    }

    .kanban-column {
        background-color: #f8f9fa;
        border-radius: 8px;
        margin: 0 5px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .kanban-column-header {
        padding: 15px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .kanban-column-header h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .kanban-column-body {
        padding: 10px;
        min-height: 500px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .kanban-card {
        background: white;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: move;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-left: 4px solid #dc3545;
    }

    /* Colores según estado */
    .kanban-card.status-nuevo {
        border-left-color: #17a2b8;
    }

    .kanban-card.status-en_proceso,
    .kanban-card.status-en_proceso.started {
        border-left-color: #ffc107 !important;
        background: #fffbf0;
    }

    .kanban-card.status-finalizado,
    .kanban-card.status-finalizado.started {
        border-left-color: #28a745 !important;
        background: #f0f9f4;
    }

    /* Mantener el color del borde cuando está iniciada (para NUEVO) */
    .kanban-card.started.status-nuevo {
        border-left-color: #28a745;
    }

    .kanban-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .kanban-card-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }

    .kanban-card-title i {
        margin-right: 8px;
    }

    .kanban-card-title.text-danger {
        color: #dc3545;
    }

    .kanban-card-title.text-success {
        color: #28a745;
    }

    .kanban-card-title.text-warning {
        color: #ffc107;
    }

    .kanban-card-description {
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .kanban-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e9ecef;
    }

    .kanban-card-dates {
        font-size: 12px;
        color: #6c757d;
    }

    .kanban-card-dates i {
        margin-right: 5px;
    }

    .kanban-card-actions {
        display: flex;
        gap: 5px;
    }

    .kanban-card-actions .btn {
        padding: 4px 8px;
        font-size: 12px;
    }

    .sortable-ghost {
        opacity: 0.4;
        background: #e3f2fd;
    }

    .sortable-drag {
        opacity: 0.8;
    }

    /* Gantt Chart */
    #ganttChart {
        width: 100%;
        height: 500px;
        overflow-x: auto;
    }

    .gantt .bar-wrapper .bar-label {
        fill: #333;
        font-size: 12px;
    }

    .gantt .bar-wrapper.nuevo .bar {
        fill: #17a2b8;
    }

    .gantt .bar-wrapper.en_proceso .bar {
        fill: #ffc107;
    }

    .gantt .bar-wrapper.finalizado .bar {
        fill: #28a745;
    }
</style>
@stop

@section('js')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="{{ asset('js/lead-notes.js') }}"></script>

<script>
// Variables globales
let leadQuillEditor = null;
let currentLeadNoteId = null;
let calendar = null;
const LEAD_ID = {{ $lead->id }};
const CSRF_TOKEN = '{{ csrf_token() }}';

$(document).ready(function() {
    console.log('Document ready - LEAD_ID:', LEAD_ID);

    // Detectar si viene con compose=true para abrir modal
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('compose') === 'true') {
        setTimeout(function() {
            const toEmail = urlParams.get('to');
            if (toEmail) {
                $('#compose_to').val(decodeURIComponent(toEmail));
            }
            $('#composeEmailModal').modal('show');
        }, 500);
    }

    // Cargar notas al activar la pestaña (Bootstrap Tab Event)
    $('#notes-tab').on('shown.bs.tab', function(e) {
        console.log('Tab shown event triggered:', e);
        if (!leadQuillEditor) {
            initializeLeadNoteEditor();
        }
        loadLeadNotes();
    });

    // También con click directo como fallback
    $('#notes-tab').on('click', function(e) {
        console.log('Tab click event triggered:', e);
        setTimeout(function() {
            if (!leadQuillEditor) {
                initializeLeadNoteEditor();
            }
            loadLeadNotes();
        }, 100);
    });

    // Si la pestaña de notas está activa al cargar la página, cargar notas
    if ($('#notes-tab').hasClass('active')) {
        console.log('Notas tab is active on load');
        if (!leadQuillEditor) {
            initializeLeadNoteEditor();
        }
        loadLeadNotes();
    }

    // Actualizar assigned_to cuando cambia el vendedor
    $('#vendedor_select').on('change', function() {
        var userId = $(this).find(':selected').data('user-id');
        $('#assigned_to').val(userId);
    });

    // Manejar envío del formulario de asignación
    $('#assignForm').on('submit', function(e) {
        e.preventDefault();

        var vendedorId = $('#vendedor_select').val();
        var assignedTo = $('#assigned_to').val();

        if (!vendedorId || !assignedTo) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor seleccione un vendedor'
            });
            return;
        }

        $.ajax({
            url: '{{ route("crm.leads.assign", $lead->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                vendedor_id: vendedorId,
                assigned_to: assignedTo
            },
            success: function(response) {
                if (response.success) {
                    $('#assignModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al asignar',
                    text: xhr.responseJSON?.message || 'Error desconocido'
                });
            }
        });
    });
});

// ==================== FUNCIONES DE NOTAS ====================

function initializeLeadNoteEditor() {
    leadQuillEditor = new Quill('#leadNoteContent', {
        theme: 'snow',
        placeholder: 'Escribe tu nota aquí...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });
}

function showNoteForm() {
    if (!leadQuillEditor) {
        initializeLeadNoteEditor();
    }

    $('#noteFormContainer').slideDown();
    $('#noteFormTitle').text('Nueva Nota');
    $('#saveLeadNoteText').text('Guardar');
    $('#deleteLeadNoteBtn').hide();

    // Quitar resaltado de edición
    $('#noteFormCard').removeClass('editing');
    $('#noteFormHeader').removeClass('editing');
    $('#noteFormHeaderTitle').removeClass('editing');

    resetLeadNoteForm();
}

function cancelNoteForm() {
    $('#noteFormContainer').slideUp();

    // Quitar resaltado de edición
    $('#noteFormCard').removeClass('editing');
    $('#noteFormHeader').removeClass('editing');
    $('#noteFormHeaderTitle').removeClass('editing');

    resetLeadNoteForm();
}

function resetLeadNoteForm() {
    currentLeadNoteId = null;
    $('#leadNoteId').val('');
    $('#leadNoteType').val('note');
    $('#leadNotePinned').prop('checked', false);

    if (leadQuillEditor) {
        leadQuillEditor.setContents([]);
    }
}

function loadLeadNotes() {
    console.log('loadLeadNotes iniciado para LEAD_ID:', LEAD_ID);
    const notesListDiv = document.getElementById('leadNotesList');

    if (!notesListDiv) {
        console.error('Elemento leadNotesList no encontrado');
        return;
    }

    notesListDiv.innerHTML = '<div class="text-center text-muted p-3"><i class="fas fa-spinner fa-spin"></i> Cargando notas...</div>';

    // Usar fetch directamente para mejor debugging
    fetch(`/crm/leads/${LEAD_ID}/notes`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response recibido:', response);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Data recibido:', data);
        const notes = data.notes || [];
        console.log('Notas procesadas:', notes);

        if (notes.length === 0) {
            notesListDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-inbox"></i> No hay notas registradas.</div>';
            updateNotesTabBadge(0);
            return;
        }

        let html = '';
        notes.forEach(note => {
                const icon = getNoteIcon(note.type);
                const color = getNoteColor(note.type);
                const isPinned = note.is_pinned ? '<i class="fas fa-thumbtack text-warning"></i> ' : '';
                const date = new Date(note.created_at).toLocaleDateString('es-ES', {
                    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                html += `
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5>
                                    ${isPinned}
                                    <i class="${icon} text-${color}"></i>
                                    ${note.type.charAt(0).toUpperCase() + note.type.slice(1)}
                                </h5>
                                <div>
                                    <small class="text-muted">${date}</small>
                                    <button class="btn btn-sm btn-primary ml-2" onclick="loadLeadNoteForEdit(${note.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                ${note.content}
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Por: ${note.user ? note.user.name : 'Usuario'}</small>
                            </div>
                        </div>
                    </div>
                `;
            });

            notesListDiv.innerHTML = html;
            updateNotesTabBadge(notes.length);
        })
        .catch(error => {
            console.error('Error al cargar notas:', error);
            notesListDiv.innerHTML = '<div class="alert alert-danger">Error al cargar notas: ' + error.message + '</div>';
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
        'email': 'primary',
        'call': 'success',
        'meeting': 'warning',
        'whatsapp': 'success',
        'note': 'info'
    };
    return colors[type] || 'secondary';
}

function updateNotesTabBadge(count) {
    const badge = $('#notes-tab .badge');

    if (count > 0) {
        if (badge.length) {
            badge.text(count);
        } else {
            $('#notes-tab').append(`<span class="badge badge-danger">${count}</span>`);
        }
    } else {
        badge.remove();
    }
}

function loadLeadNoteForEdit(noteId) {
    if (!leadQuillEditor) {
        initializeLeadNoteEditor();
    }

    leadQuillEditor.enable(false);

    fetch(`/crm/leads/${LEAD_ID}/notes/${noteId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const note = data.note;
        currentLeadNoteId = note.id;
        $('#leadNoteId').val(note.id);
        $('#leadNoteType').val(note.type);
        $('#leadNotePinned').prop('checked', note.is_pinned);

        if (leadQuillEditor) {
            leadQuillEditor.enable(true);
            leadQuillEditor.root.innerHTML = note.content || '';
        }

        $('#noteFormTitle').text('Editar Nota');
        $('#saveLeadNoteText').text('Actualizar');
        $('#deleteLeadNoteBtn').show();

        // Agregar resaltado azul para modo edición
        $('#noteFormCard').addClass('editing');
        $('#noteFormHeader').addClass('editing');
        $('#noteFormHeaderTitle').addClass('editing');

        $('#noteFormContainer').slideDown();

        // Scroll al formulario
        $('html, body').animate({
            scrollTop: $('#noteFormContainer').offset().top - 100
        }, 500);
    })
    .catch(error => {
        console.error('Error al cargar nota:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo cargar la nota'
        });
        if (leadQuillEditor) {
            leadQuillEditor.enable(true);
        }
    });
}

function saveLeadNote() {
    if (!leadQuillEditor) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Editor no inicializado'
        });
        return;
    }

    const content = leadQuillEditor.root.innerHTML.trim();
    const textContent = leadQuillEditor.getText().trim();
    const type = $('#leadNoteType').val();
    const isPinned = $('#leadNotePinned').is(':checked');
    const noteId = $('#leadNoteId').val();

    if (!textContent || textContent.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Por favor escribe el contenido de la nota'
        });
        return;
    }

    const url = noteId
        ? `/crm/leads/${LEAD_ID}/notes/${noteId}`
        : `/crm/leads/${LEAD_ID}/notes`;

    const method = noteId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            content: content,
            nota_sin_formato: textContent,
            type: type,
            is_pinned: isPinned
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: noteId ? 'Nota actualizada exitosamente' : 'Nota guardada exitosamente',
            timer: 2000,
            showConfirmButton: false
        });

        // Remover resaltado de edición
        $('#noteFormCard').removeClass('editing');
        $('#noteFormHeader').removeClass('editing');
        $('#noteFormHeaderTitle').removeClass('editing');

        loadLeadNotes();
        cancelNoteForm();
    })
    .catch(error => {
        console.error('Error al guardar nota:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo guardar la nota'
        });
    });
}

function deleteLeadNote() {
    const noteId = $('#leadNoteId').val();

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

        fetch(`/crm/leads/${LEAD_ID}/notes/${noteId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Nota eliminada exitosamente',
                timer: 2000,
                showConfirmButton: false
            });
            loadLeadNotes();
            cancelNoteForm();
        })
        .catch(error => {
            console.error('Error al eliminar nota:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo eliminar la nota'
            });
        });
    });
}

// ==================== FUNCIONES DE TAREAS KANBAN/GANTT ====================

// Inicializar Sortable en las columnas del Kanban
let sortableInstances = {};

function initializeSortable() {
    // Destruir instancias existentes
    Object.keys(sortableInstances).forEach(key => {
        if (sortableInstances[key]) {
            sortableInstances[key].destroy();
        }
    });
    sortableInstances = {};

    // Crear nuevas instancias
    ['nuevo', 'en_proceso', 'finalizado'].forEach(status => {
        const el = document.getElementById('column-' + status);
        if (el) {
            sortableInstances[status] = new Sortable(el, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    const taskId = evt.item.dataset.taskId;
                    const newStatus = evt.to.dataset.status;
                    const newPosition = evt.newIndex;

                    updateTaskPosition(taskId, newStatus, newPosition);
                }
            });
        }
    });
}

// Cambiar vista Kanban/Calendario
$('#kanbanViewBtn').on('click', function() {
    $('#kanbanView').show();
    $('#calendarView').hide();
    loadTasks();
});

$('#calendarViewBtn').on('click', function() {
    $('#kanbanView').hide();
    $('#calendarView').show();
    loadCalendarData();
});

// Cargar tareas al activar la pestaña
$('#tasks-tab').on('shown.bs.tab', function() {
    loadTasks();
});

// Cargar tareas
function loadTasks() {
    fetch(`/crm/leads/${LEAD_ID}/tasks`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderKanbanBoard(data.tasks);
        }
    })
    .catch(error => console.error('Error al cargar tareas:', error));
}

// Renderizar tablero Kanban
function renderKanbanBoard(tasks) {
    ['nuevo', 'en_proceso', 'finalizado'].forEach(status => {
        const column = document.getElementById('column-' + status);
        const badge = document.getElementById('badge-' + status);
        const tasksForStatus = tasks[status] || [];

        badge.textContent = tasksForStatus.length;

        if (tasksForStatus.length === 0) {
            column.innerHTML = getEmptyColumnHtml(status);
        } else {
            column.innerHTML = '';
            tasksForStatus.forEach(task => {
                column.innerHTML += getTaskCardHtml(task);
            });
        }
    });

    initializeSortable();
}

// HTML para columna vacía
function getEmptyColumnHtml(status) {
    const icons = {
        'nuevo': 'fa-inbox',
        'en_proceso': 'fa-spinner',
        'finalizado': 'fa-check-circle'
    };
    const labels = {
        'nuevo': 'nuevas',
        'en_proceso': 'en proceso',
        'finalizado': 'finalizadas'
    };

    return `
        <div class="text-center text-muted py-4">
            <i class="fas ${icons[status]} fa-2x"></i>
            <p>No hay tareas ${labels[status]}</p>
        </div>
    `;
}

// HTML para tarjeta de tarea
function getTaskCardHtml(task) {
    const status = task.status;
    const isStarted = task.is_started;

    // Definir iconos y clases según estado
    let titleIcon, titleClass, btnClass, btnIcon;

    if (status === 'finalizado') {
        titleIcon = 'fa-check-circle';
        titleClass = 'text-success';
        btnClass = 'btn-success';
        btnIcon = 'fa-check';
    } else if (status === 'en_proceso') {
        titleIcon = 'fa-clock';
        titleClass = 'text-warning';
        btnClass = 'btn-warning';
        btnIcon = 'fa-clock';
    } else {
        // nuevo
        titleIcon = isStarted ? 'fa-play-circle' : 'fa-circle';
        titleClass = isStarted ? 'text-success' : 'text-danger';
        btnClass = isStarted ? 'btn-success' : 'btn-outline-danger';
        btnIcon = isStarted ? 'fa-check' : 'fa-times';
    }

    return `
        <div class="kanban-card status-${status}" data-task-id="${task.id}" ondblclick="editTask(${task.id})">
            <div class="kanban-card-title ${titleClass}">
                <i class="fas ${titleIcon}"></i>
                ${task.title}
            </div>
            ${task.description ? `<div class="kanban-card-description">${task.description}</div>` : ''}
            <div class="kanban-card-footer">
                <div class="kanban-card-dates">
                    <div><i class="fas fa-play"></i> ${formatDate(task.start_date)}</div>
                    <div><i class="fas fa-flag-checkered"></i> ${formatDate(task.end_date)}</div>
                </div>
                <div class="kanban-card-actions">
                    <button class="btn btn-sm ${btnClass}"
                            onclick="toggleTaskStarted(event, ${task.id})">
                        <i class="fas ${btnIcon}"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="editTask(${task.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Formatear fecha y hora
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

// Formatear fecha para input datetime-local (YYYY-MM-DDTHH:MM)
function formatDateTimeLocal(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Resetear modal de tarea (para botón Nueva Tarea)
function resetTaskModal() {
    document.getElementById('taskId').value = '';
    document.getElementById('taskForm').reset();
    document.getElementById('taskModalTitle').textContent = 'Nueva Tarea';
    document.getElementById('deleteTaskBtn').style.display = 'none';
}

// Editar tarea
function editTask(taskId) {
    fetch(`/crm/leads/${LEAD_ID}/tasks/${taskId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const task = data.task;
            document.getElementById('taskId').value = task.id;
            document.getElementById('taskTitle').value = task.title;
            document.getElementById('taskDescription').value = task.description || '';

            // Formatear fechas para inputs type="datetime-local" (YYYY-MM-DDTHH:MM)
            document.getElementById('taskStartDate').value = task.start_date ? formatDateTimeLocal(task.start_date) : '';
            document.getElementById('taskEndDate').value = task.end_date ? formatDateTimeLocal(task.end_date) : '';
            document.getElementById('taskIsStarted').checked = task.is_started;

            document.getElementById('taskModalTitle').textContent = 'Editar Tarea';
            document.getElementById('deleteTaskBtn').style.display = 'inline-block';

            // Abrir modal con Bootstrap nativo
            const modalEl = document.getElementById('taskModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    })
    .catch(error => console.error('Error al cargar tarea:', error));
}

// Guardar tarea
function saveTask() {
    const taskId = document.getElementById('taskId').value;
    const title = document.getElementById('taskTitle').value.trim();
    const description = document.getElementById('taskDescription').value.trim();
    const startDate = document.getElementById('taskStartDate').value;
    const endDate = document.getElementById('taskEndDate').value;
    const isStarted = document.getElementById('taskIsStarted').checked;

    if (!title || !startDate || !endDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos incompletos',
            text: 'Por favor complete los campos obligatorios'
        });
        return;
    }

    const url = taskId
        ? `/crm/leads/${LEAD_ID}/tasks/${taskId}`
        : `/crm/leads/${LEAD_ID}/tasks`;

    const method = taskId ? 'PUT' : 'POST';

    const payload = {
        title: title,
        description: description,
        start_date: startDate + ':00', // Agregar segundos
        end_date: endDate + ':00', // Agregar segundos
        is_started: isStarted
    };

    console.log('Guardando tarea con payload:', payload);

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // Cerrar modal usando DOM
                const closeButton = document.querySelector('#taskModal .close');
                if (closeButton) closeButton.click();
                loadTasks();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error al guardar la tarea'
            });
        }
    })
    .catch(error => {
        console.error('Error al guardar tarea:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar la tarea'
        });
    });
}

// Eliminar tarea
function deleteTask() {
    const taskId = document.getElementById('taskId').value;

    Swal.fire({
        title: '¿Está seguro?',
        text: '¿Desea eliminar esta tarea?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/crm/leads/${LEAD_ID}/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminada!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Cerrar modal usando DOM
                        const closeButton = document.querySelector('#taskModal .close');
                        if (closeButton) closeButton.click();
                        loadTasks();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al eliminar la tarea'
                    });
                }
            })
            .catch(error => {
                console.error('Error al eliminar tarea:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar la tarea'
                });
            });
        }
    });
}

// Toggle estado iniciado
function toggleTaskStarted(event, taskId) {
    event.stopPropagation();

    fetch(`/crm/leads/${LEAD_ID}/tasks/${taskId}/toggle-started`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTasks();
        }
    })
    .catch(error => console.error('Error al cambiar estado:', error));
}

// Actualizar posición de tarea (drag & drop)
function updateTaskPosition(taskId, newStatus, newPosition) {
    fetch(`/crm/leads/${LEAD_ID}/tasks/${taskId}/position`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({
            status: newStatus,
            position: newPosition,
            reset_started: newStatus === 'nuevo' // Si vuelve a nuevo, resetear is_started
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTasks();
        }
    })
    .catch(error => {
        console.error('Error al actualizar posición:', error);
        loadTasks(); // Recargar para restaurar el estado correcto
    });
}

// Cargar datos para Calendario
function loadCalendarData() {
    fetch(`/crm/leads/${LEAD_ID}/tasks`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const allTasks = [...data.tasks.nuevo, ...data.tasks.en_proceso, ...data.tasks.finalizado];
            if (allTasks.length > 0) {
                document.getElementById('taskCalendar').style.display = 'block';
                document.getElementById('calendarEmpty').style.display = 'none';
                renderCalendar(allTasks);
            } else {
                document.getElementById('taskCalendar').style.display = 'none';
                document.getElementById('calendarEmpty').style.display = 'block';
            }
        }
    })
    .catch(error => console.error('Error al cargar datos del calendario:', error));
}

// Renderizar Calendario
function renderCalendar(tasks) {
    // Destruir calendario existente
    if (calendar) {
        calendar.destroy();
    }

    // Convertir tareas a eventos del calendario
    const events = tasks.map(task => {
        let backgroundColor, borderColor;
        if (task.status === 'finalizado') {
            backgroundColor = '#28a745';
            borderColor = '#28a745';
        } else if (task.status === 'en_proceso') {
            backgroundColor = '#ffc107';
            borderColor = '#ffc107';
        } else {
            backgroundColor = '#17a2b8';
            borderColor = '#17a2b8';
        }

        return {
            id: task.id,
            title: task.title,
            start: task.start_date,
            end: task.end_date,
            backgroundColor: backgroundColor,
            borderColor: borderColor,
            extendedProps: {
                description: task.description,
                status: task.status,
                is_started: task.is_started
            }
        };
    });

    // Crear calendario
    const calendarEl = document.getElementById('taskCalendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        events: events,
        eventClick: function(info) {
            editTask(parseInt(info.event.id));
        },
        editable: true,
        eventDrop: function(info) {
            updateTaskDates(parseInt(info.event.id), info.event.start, info.event.end);
        },
        eventResize: function(info) {
            updateTaskDates(parseInt(info.event.id), info.event.start, info.event.end);
        },
        height: 'auto',
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short',
            hour12: true
        },
        slotLabelFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short',
            hour12: true
        }
    });

    calendar.render();
}

// Actualizar fechas de tarea desde Calendario
function updateTaskDates(taskId, start, end) {
    fetch(`/crm/leads/${LEAD_ID}/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({
            start_date: start.toISOString(),
            end_date: end ? end.toISOString() : start.toISOString()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                text: 'Fechas actualizadas correctamente',
                timer: 1500,
                showConfirmButton: false
            });
        }
    })
    .catch(error => {
        console.error('Error al actualizar fechas:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron actualizar las fechas'
        });
        loadCalendarData(); // Recargar para restaurar estado
    });
}

// Manejar eliminación de lead con SweetAlert
$(document).ready(function() {
    $('#btnDeleteLead').on('click', function() {
        const leadId = $(this).data('lead-id');

        Swal.fire({
            title: '¿Está seguro?',
            html: `
                <p>Esta acción eliminará el lead y toda la información relacionada:</p>
                <ul style="text-align: left; margin: 10px auto; display: inline-block;">
                    <li>Actividades</li>
                    <li>Tareas</li>
                    <li>Notas</li>
                    <li>Conversaciones</li>
                </ul>
                <p><strong>Esta acción no se puede deshacer.</strong></p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Eliminando...',
                    html: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Hacer la petición DELETE
                $.ajax({
                    url: `/crm/leads/${leadId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = '{{ route("crm.leads.index") }}';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'No se pudo eliminar el lead'
                            });
                        }
                    },
                    error: function(xhr) {
                        let message = 'Error al eliminar el lead';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                });
            }
        });
    });
});

</script>
@stop
