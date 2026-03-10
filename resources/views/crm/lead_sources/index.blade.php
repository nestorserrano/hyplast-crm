@extends('adminlte::page')

@section('title', 'Fuentes de Leads')

@section('content_header')
    <h1><i class="fas fa-bullhorn"></i> Fuentes de Leads</h1>
@stop

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="card-title">Listado de Fuentes</h3>
                </div>
                <div class="col-md-6 text-right">
                    @can('create.crm.lead_sources')
                    <button type="button" class="btn btn-success" onclick="openModal('create')">
                        <i class="fas fa-plus"></i> Nueva Fuente
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="lead-sources-table" class="table table-bordered table-hover table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Vista Previa</th>
                        <th>Descripción</th>
                        <th>Orden</th>
                        <th>Leads</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Lateral -->
<div class="modal fade" id="leadSourceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-slideout" role="document">
        <div class="modal-content" style="display: flex; flex-direction: column;">
            <div class="card-header">
                <h3 class="card-title" id="modalTitle">Nueva Fuente</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <form id="leadSourceForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                @csrf
                <input type="hidden" id="source_id" name="source_id">
                <div class="card-body" style="flex: 1; overflow-y: auto;">
                    <div class="form-group">
                        <label for="name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="code">Código <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control" required>
                        <small class="text-muted">Código único para identificar la fuente (sin espacios)</small>
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="color">Color <span class="text-danger">*</span></label>
                        <select name="color" id="color" class="form-control" required>
                            <option value="primary">Azul (Primary)</option>
                            <option value="success">Verde (Success)</option>
                            <option value="info">Celeste (Info)</option>
                            <option value="warning">Amarillo (Warning)</option>
                            <option value="danger">Rojo (Danger)</option>
                            <option value="secondary">Gris (Secondary)</option>
                            <option value="dark">Negro (Dark)</option>
                            <option value="purple">Morado (Purple)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="order">Orden <span class="text-danger">*</span></label>
                        <input type="number" name="order" id="order" class="form-control" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" checked>
                            <label class="custom-control-label" for="is_active">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" onclick="enableEditSource()" id="btnEditSource" style="display: none;">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnSaveSource">
                        <i class="fas fa-save"></i> <span id="btnText">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .d-flex.gap-1 > * {
        margin-right: 5px;
    }
    .d-flex.gap-1 > *:last-child {
        margin-right: 0;
    }
    #lead-sources-table .btn-sm {
        white-space: nowrap;
    }

    /* Modal lateral estilo Google Contacts */
    .modal-dialog-slideout {
        position: fixed !important;
        margin: 0 !important;
        width: 450px !important;
        max-width: 90% !important;
        height: 100% !important;
        right: 0 !important;
        top: 0 !important;
        left: auto !important;
        bottom: 0 !important;
        transform: translateX(100%) !important;
        transition: transform 0.3s ease-out !important;
        max-height: none !important;
    }

    #leadSourceModal.show .modal-dialog-slideout {
        transform: translateX(0) !important;
    }

    .modal-dialog-slideout .modal-content {
        height: 100% !important;
        border-radius: 0 !important;
        border: none !important;
        box-shadow: -2px 0 8px rgba(0,0,0,0.15) !important;
        overflow: hidden !important;
    }

    .modal-dialog-slideout .card-header {
        border-radius: 0 !important;
        padding: 0.75rem 1.25rem !important;
        flex-shrink: 0;
        background-color: #fff !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .modal-dialog-slideout .card-header .card-title {
        font-size: 1.1rem !important;
        font-weight: 400 !important;
        margin: 0 !important;
    }

    .modal-dialog-slideout .card-body {
        overflow-y: auto !important;
        padding: 1.25rem !important;
        flex: 1 1 auto;
    }

    .modal-dialog-slideout .card-footer {
        padding: 0.75rem 1.25rem !important;
        background-color: #f8f9fa !important;
        border-top: 1px solid #dee2e6 !important;
        flex-shrink: 0;
    }

    .modal-dialog-slideout .card-footer .btn {
        margin-right: 5px;
    }

    /* Anular animación por defecto de Bootstrap */
    #leadSourceModal .modal-dialog {
        transition: none !important;
    }

    /* Backdrop */
    #leadSourceModal .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.3);
    }
</style>
@stop

@section('js')
@include('scripts.datatables.datatables-lead-sources')
@stop
