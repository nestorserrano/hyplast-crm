<!-- Modal Drawer para Crear/Editar Lead -->
<div id="leadModal" class="modal-drawer" style="display: none;">
    <div class="modal-drawer-overlay" onclick="closeLeadModal()"></div>
    <div class="modal-drawer-content">
        <div class="modal-drawer-header">
            <h4 id="modalTitle">Crear Contacto</h4>
            <button type="button" class="close" onclick="closeLeadModal()">
                <span>&times;</span>
            </button>
        </div>

        <form id="leadForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="lead_id" id="lead_id" value="">

            <div class="modal-drawer-body">
                <!-- Correo -->
                <div class="form-group">
                    <label for="email">Correo</label>
                    <input type="email" name="email" id="modal_email" class="form-control" placeholder="correo@ejemplo.com">
                    <span class="invalid-feedback d-none" id="error_email"></span>
                </div>

                <!-- Nombre -->
                <div class="form-group">
                    <label for="name">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="modal_name" class="form-control" required>
                    <span class="invalid-feedback d-none" id="error_name"></span>
                </div>

                <!-- Teléfono -->
                <div class="form-group">
                    <label for="phone">Número de teléfono <span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="modal_phone" class="form-control" required>
                    <span class="invalid-feedback d-none" id="error_phone"></span>
                </div>

                <!-- Empresa -->
                <div class="form-group">
                    <label for="company">Empresa</label>
                    <input type="text" name="company" id="modal_company" class="form-control">
                    <span class="invalid-feedback d-none" id="error_company"></span>
                </div>

                <!-- País -->
                <div class="form-group">
                    <label for="country_id">País</label>
                    <select name="country_id" id="modal_country_id" class="form-control">
                        <option value="">Seleccione un país...</option>
                        @foreach($countries ?? [] as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                    <span class="invalid-feedback d-none" id="error_country_id"></span>
                </div>

                <!-- Estado -->
                <div class="form-group">
                    <label for="state_id">Estado</label>
                    <select name="state_id" id="modal_state_id" class="form-control" disabled>
                        <option value="">Primero seleccione un país...</option>
                    </select>
                    <span class="invalid-feedback d-none" id="error_state_id"></span>
                </div>

                <!-- Ciudad -->
                <div class="form-group">
                    <label for="city_id">Ciudad</label>
                    <select name="city_id" id="modal_city_id" class="form-control" disabled>
                        <option value="">Primero seleccione un estado...</option>
                    </select>
                    <span class="invalid-feedback d-none" id="error_city_id"></span>
                </div>

                <!-- Estado del lead -->
                <div class="form-group">
                    <label for="lead_status_id">Estado del lead</label>
                    <select name="lead_status_id" id="modal_lead_status_id" class="form-control">
                        <option value="">Seleccione un estado...</option>
                        @foreach($statuses ?? [] as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <span class="invalid-feedback d-none" id="error_lead_status_id"></span>
                </div>

                <!-- Canal preferido -->
                <div class="form-group">
                    <label for="preferred_channel">Canal de contacto preferido</label>
                    <select name="preferred_channel" id="modal_preferred_channel" class="form-control">
                        <option value="">Seleccione un canal...</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="phone">Teléfono</option>
                        <option value="sms">SMS</option>
                    </select>
                    <span class="invalid-feedback d-none" id="error_preferred_channel"></span>
                </div>

                <!-- Vendedor asignado -->
                <div class="form-group">
                    <label for="assigned_to">Asignado a</label>
                    <select name="assigned_to" id="modal_assigned_to" class="form-control">
                        <option value="">Seleccione un vendedor...</option>
                        @foreach($vendedores ?? [] as $vendedor)
                            <option value="{{ $vendedor->id }}" data-vendedor-id="{{ $vendedor->vendedor_id }}">
                                {{ $vendedor->vendedor_id }} - {{ $vendedor->vendedorSoftland ? $vendedor->vendedorSoftland->NOMBRE : $vendedor->vendedor_nombre }}
                            </option>
                        @endforeach
                    </select>
                    <span class="invalid-feedback d-none" id="error_assigned_to"></span>
                </div>

                <!-- Fuente -->
                <div class="form-group">
                    <label for="source">Fuente</label>
                    <select name="lead_source_id" id="modal_lead_source_id" class="form-control">
                        <option value="">Seleccione una fuente...</option>
                        @foreach($sources ?? [] as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                        @endforeach
                    </select>
                    <span class="invalid-feedback d-none" id="error_lead_source_id"></span>
                </div>

                <!-- Prioridad -->
                <div class="form-group">
                    <label for="priority">Prioridad</label>
                    <select name="priority" id="modal_priority" class="form-control">
                        <option value="2">Media</option>
                        <option value="1">Alta</option>
                        <option value="3">Baja</option>
                    </select>
                    <span class="invalid-feedback d-none" id="error_priority"></span>
                </div>

                <!-- Fecha esperada de cierre -->
                <div class="form-group">
                    <label for="expected_close_date">Fecha esperada de cierre</label>
                    <input type="date" name="expected_close_date" id="modal_expected_close_date" class="form-control">
                    <span class="invalid-feedback d-none" id="error_expected_close_date"></span>
                </div>

                <!-- Notas -->
                <div class="form-group">
                    <label for="notes">Notas</label>
                    <textarea name="notes" id="modal_notes" class="form-control" rows="3"></textarea>
                    <span class="invalid-feedback d-none" id="error_notes"></span>
                </div>
            </div>

            <div class="modal-drawer-footer">
                <button type="button" class="btn btn-secondary" onclick="closeLeadModal()" id="btnCancel">Cancelar</button>
                <a href="#" class="btn btn-info" id="btnGoToDetail" style="display: none;">
                    <i class="fas fa-eye"></i> Ir al detalle
                </a>
                <button type="button" class="btn btn-warning" onclick="enableEdit()" id="btnEdit" style="display: none;">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button type="submit" name="action" value="save_and_new" class="btn btn-primary" id="btnSaveAndNew" style="display: none;">
                    <i class="fas fa-plus-circle"></i> Crear y agregar otro
                </button>
                <button type="submit" name="action" value="save" class="btn btn-success" id="btnSave">
                    <i class="fas fa-save"></i> Crear
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal-drawer {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
}

.modal-drawer-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-drawer-content {
    position: absolute;
    top: 0;
    right: 0;
    width: 500px;
    max-width: 90%;
    height: 100%;
    background: white;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}

.modal-drawer-header {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-drawer-header h4 {
    margin: 0;
    font-size: 1.25rem;
}

.modal-drawer-header .close {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    padding: 0;
    color: #000;
    opacity: 0.5;
}

.modal-drawer-header .close:hover {
    opacity: 1;
}

.modal-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    max-height: calc(100vh - 140px); /* Asegurar que tenga altura máxima */
}

.modal-drawer-footer {
    padding: 15px 20px;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.modal-drawer-body .form-group {
    margin-bottom: 1rem;
}

.modal-drawer-body .form-group label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    display: block;
}
</style>
