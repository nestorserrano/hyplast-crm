@extends('adminlte::page')

@section('title', 'Crear Lead')

@section('content_header')
    <h1>Crear Nuevo Lead</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información del Lead</h3>
                <div class="card-tools">
                    <a href="{{ route('crm.leads.index') }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <form action="{{ route('crm.leads.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-user"></i> Datos de Contacto</h5>

                            <div class="form-group">
                                <label for="company">Empresa</label>
                                <input type="text" class="form-control @error('company') is-invalid @enderror"
                                       id="company" name="company" value="{{ old('company') }}">
                                @error('company')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="phone">Teléfono <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="website">Sitio Web</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror"
                                       id="website" name="website" value="{{ old('website') }}" placeholder="https://www.ejemplo.com">
                                @error('website')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Información de Ventas -->
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-chart-line"></i> Información de Venta</h5>

                            <div class="form-group">
                                <label for="lead_status_id">Estado <span class="text-danger">*</span></label>
                                <select class="form-control @error('lead_status_id') is-invalid @enderror"
                                        id="lead_status_id" name="lead_status_id" required>
                                    <option value="">Seleccione un estado</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ old('lead_status_id') == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lead_status_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            @can('crm.leads.assign')
                            <div class="form-group">
                                <label for="assigned_to">Asignar a</label>
                                <select class="form-control @error('assigned_to') is-invalid @enderror"
                                        id="assigned_to" name="assigned_to">
                                    <option value="">Sin asignar</option>
                                    @foreach($vendedores as $vendedor)
                                        <option value="{{ $vendedor->id }}" {{ old('assigned_to') == (string)$vendedor->id ? 'selected' : '' }}>
                                            {{ $vendedor->vendedor_id }} - {{ $vendedor->vendedorSoftland ? $vendedor->vendedorSoftland->NOMBRE : $vendedor->vendedor_nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            @endcan

                            <div class="form-group">
                                <label for="source">Fuente</label>
                                <select class="form-control @error('source') is-invalid @enderror"
                                        id="source" name="source">
                                    <option value="">Seleccione una fuente</option>
                                    <option value="whatsapp" {{ old('source') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                    <option value="phone" {{ old('source') == 'phone' ? 'selected' : '' }}>Teléfono</option>
                                    <option value="email" {{ old('source') == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="website" {{ old('source') == 'website' ? 'selected' : '' }}>Sitio Web</option>
                                    <option value="referral" {{ old('source') == 'referral' ? 'selected' : '' }}>Referido</option>
                                    <option value="other" {{ old('source') == 'other' ? 'selected' : '' }}>Otro</option>
                                </select>
                                @error('source')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="priority">Prioridad</label>
                                <select class="form-control @error('priority') is-invalid @enderror"
                                        id="priority" name="priority">
                                    <option value="1" {{ old('priority', 2) == 1 ? 'selected' : '' }}>Alta</option>
                                    <option value="2" {{ old('priority', 2) == 2 ? 'selected' : '' }}>Media</option>
                                    <option value="3" {{ old('priority', 2) == 3 ? 'selected' : '' }}>Baja</option>
                                </select>
                                @error('priority')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="country_id">País</label>
                                <select class="form-control @error('country_id') is-invalid @enderror"
                                        id="country_id" name="country_id">
                                    <option value="">Seleccione un país...</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="state_id">Estado</label>
                                <select class="form-control @error('state_id') is-invalid @enderror"
                                        id="state_id" name="state_id" disabled>
                                    <option value="">Primero seleccione un país...</option>
                                </select>
                                @error('state_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="city_id">Ciudad</label>
                                <select class="form-control @error('city_id') is-invalid @enderror"
                                        id="city_id" name="city_id" disabled>
                                    <option value="">Primero seleccione un estado...</option>
                                </select>
                                @error('city_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="expected_close_date">Fecha Estimada de Cierre</label>
                                <input type="date" class="form-control @error('expected_close_date') is-invalid @enderror"
                                       id="expected_close_date" name="expected_close_date" value="{{ old('expected_close_date') }}">
                                @error('expected_close_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">Notas</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Lead
                    </button>
                    <a href="{{ route('crm.leads.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
<script>
$(document).ready(function() {
    // Combo en cascada: País -> Estados
    $('#country_id').on('change', function() {
        const countryId = $(this).val();
        const stateSelect = $('#state_id');
        const citySelect = $('#city_id');

        // Limpiar y deshabilitar estados y ciudades
        stateSelect.html('<option value="">Cargando estados...</option>').prop('disabled', true);
        citySelect.html('<option value="">Primero seleccione un estado...</option>').prop('disabled', true);

        if (!countryId) {
            stateSelect.html('<option value="">Primero seleccione un país...</option>');
            return;
        }

        // Cargar estados
        $.ajax({
            url: '/api/countries/' + countryId + '/states',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let options = '<option value="">Seleccione un estado...</option>';
                    response.data.forEach(function(state) {
                        options += '<option value="' + state.id + '">' + state.name + '</option>';
                    });
                    stateSelect.html(options).prop('disabled', false);
                } else {
                    stateSelect.html('<option value="">No hay estados disponibles</option>').prop('disabled', true);
                }
            },
            error: function() {
                stateSelect.html('<option value="">Error al cargar estados</option>').prop('disabled', true);
            }
        });
    });

    // Combo en cascada: Estado -> Ciudades
    $('#state_id').on('change', function() {
        const stateId = $(this).val();
        const citySelect = $('#city_id');

        citySelect.html('<option value="">Cargando ciudades...</option>').prop('disabled', true);

        if (!stateId) {
            citySelect.html('<option value="">Primero seleccione un estado...</option>');
            return;
        }

        // Cargar ciudades
        $.ajax({
            url: '/api/states/' + stateId + '/cities',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let options = '<option value="">Seleccione una ciudad...</option>';
                    response.data.forEach(function(city) {
                        options += '<option value="' + city.id + '">' + city.name + '</option>';
                    });
                    citySelect.html(options).prop('disabled', false);
                } else {
                    citySelect.html('<option value="">No hay ciudades disponibles</option>').prop('disabled', true);
                }
            },
            error: function() {
                citySelect.html('<option value="">Error al cargar ciudades</option>').prop('disabled', true);
            }
        });
    });
});
</script>
@stop
