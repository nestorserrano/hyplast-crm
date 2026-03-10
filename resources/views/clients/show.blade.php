@extends('adminlte::page')

@section('title', __('clients.client_detail'))

@section('content_header')
    <h1><i class="fas fa-user"></i> {{ __('clients.client_detail') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Información del Cliente -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h3 class="card-title mb-0">{{ __('clients.general_info') }}</h3>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="d-inline-flex align-items-center">
                                    <label class="mb-0 mr-2 font-weight-bold" for="tipo_moneda_detalle" style="white-space:nowrap;">Moneda:</label>
                                    <select class="form-control form-control-sm d-inline-block mr-2" id="tipo_moneda_detalle" style="width: 170px;">
                                        <option value="local">Moneda Local (RD$)</option>
                                        <option value="dolar">Dólares (US$)</option>
                                    </select>
                                    <a href="{{ route('clients.index') }}" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-arrow-left"></i> {{ __('clients.back') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">{{ __('clients.code') }}:</th>
                                        <td><strong>{{ $cliente->CLIENTE }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('clients.name') }}:</th>
                                        <td><strong>{{ $cliente->NOMBRE }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('clients.contact') }}:</th>
                                        <td>{{ $cliente->CONTACTO ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('clients.address') }}:</th>
                                        <td>{{ $cliente->DIRECCION ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('clients.city') }}:</th>
                                        <td>{{ $cliente->CIUDAD ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('clients.country') }}:</th>
                                        <td>{{ $cliente->PAIS ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Límite de Crédito:</th>
                                        <td>
                                            @if($clienteCompleto && $clienteCompleto->LIMITE_CREDITO)
                                                <span class="badge badge-info">RD${{ number_format(floatval($clienteCompleto->LIMITE_CREDITO), 2) }}</span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Categoría:</th>
                                        <td>
                                            @if($categoria)
                                                <span class="badge badge-secondary">{{ $categoria->DESCRIPCION ?? $categoria->CATEGORIA_CLIENTE }}</span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Nivel de Precio:</th>
                                        <td>
                                            @if($nivelPrecio)
                                                <span class="badge badge-primary">{{ $nivelPrecio->NIVEL_PRECIO }}</span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">{{ __('clients.phone') }}:</th>
                                        <td>{{ $cliente->TELEFONO1 ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('clients.email') }}:</th>
                                        <td>{{ $cliente->E_MAIL ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('clients.status') }}:</th>
                                        <td>
                                            @if($cliente->ACTIVO === 'S')
                                                <span class="badge badge-success">{{ __('clients.active') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('clients.inactive') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Vendedor Principal:</th>
                                        <td>
                                            @if($vendedor)
                                                <i class="fas fa-user-tie"></i> {{ $vendedor->NOMBRE }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Cobrador:</th>
                                        <td>
                                            @if($cobrador)
                                                <i class="fas fa-money-check-alt"></i> {{ $cobrador->NOMBRE }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Saldo:</th>
                                        <td>
                                            <h4 class="mb-0">
                                                <span class="saldo-local badge badge-{{ floatval($cliente->SALDO_LOCAL ?? 0) > 0 ? 'danger' : 'success' }}">
                                                    RD${{ number_format(floatval($cliente->SALDO_LOCAL ?? 0), 2) }}
                                                </span>
                                                <span class="saldo-dolar badge badge-{{ floatval($cliente->SALDO_DOLAR ?? 0) > 0 ? 'danger' : 'success' }}" style="display:none;">
                                                    ${{ number_format(floatval($cliente->SALDO_DOLAR ?? 0), 2) }}
                                                </span>
                                            </h4>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($otrosVendedores && $otrosVendedores->count() > 0)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5><i class="fas fa-users"></i> Otros Vendedores Asociados</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Código</th>
                                                <th>Nombre</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($otrosVendedores as $otroVendedor)
                                            <tr>
                                                <td>{{ $otroVendedor->VENDEDOR }}</td>
                                                <td>{{ $otroVendedor->NOMBRE }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($clienteCompleto && isset($clienteCompleto->NOTA) && !empty($clienteCompleto->NOTA))
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5><i class="fas fa-sticky-note"></i> Notas</h5>
                                <div class="alert alert-info">
                                    {{ $clienteCompleto->NOTA }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning" style="cursor:pointer;" onclick="verDocumentosPendientes()" title="Clic para ver documentos pendientes">
                    <div class="inner">
                        <h3>{{ $estadisticas->documentos_pendientes ?? 0 }}</h3>
                        <p style="margin-bottom: 5px;">
                            <span class="saldo-local">RD${{ number_format(floatval($estadisticas->total_pendientes_local ?? 0), 2) }}</span>
                            <span class="saldo-dolar" style="display:none;">${{ number_format(floatval($estadisticas->total_pendientes_dolar ?? 0), 2) }}</span>
                        </p>
                        <p style="margin-bottom: 0;">Documentos Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success" style="cursor:pointer;" onclick="verFacturasCliente()" title="Clic para ver todas las facturas">
                    <div class="inner">
                        <h3>{{ $estadisticas->cantidad_facturas ?? 0 }}</h3>
                        <p style="margin-bottom: 5px;">
                            <span class="saldo-local">RD${{ number_format(floatval($estadisticas->total_facturas_local ?? 0), 2) }}</span>
                            <span class="saldo-dolar" style="display:none;">${{ number_format(floatval($estadisticas->total_facturas_dolar ?? 0), 2) }}</span>
                        </p>
                        <p style="margin-bottom: 0;">Total Facturas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary" style="cursor:pointer;" onclick="verNotasCredito()" title="Clic para ver notas de crédito">
                    <div class="inner">
                        <h3>{{ $estadisticas->cantidad_nc ?? 0 }}</h3>
                        <p style="margin-bottom: 5px;">
                            <span class="saldo-local">RD${{ number_format(floatval($estadisticas->total_nc_local ?? 0), 2) }}</span>
                            <span class="saldo-dolar" style="display:none;">${{ number_format(floatval($estadisticas->total_nc_dolar ?? 0), 2) }}</span>
                        </p>
                        <p style="margin-bottom: 0;">Notas de Crédito</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger" style="cursor:pointer;" onclick="verNotasDebito()" title="Clic para ver notas de débito">
                    <div class="inner">
                        <h3>{{ $estadisticas->cantidad_nd ?? 0 }}</h3>
                        <p style="margin-bottom: 5px;">
                            <span class="saldo-local">RD${{ number_format(floatval($estadisticas->total_nd_local ?? 0), 2) }}</span>
                            <span class="saldo-dolar" style="display:none;">${{ number_format(floatval($estadisticas->total_nd_dolar ?? 0), 2) }}</span>
                        </p>
                        <p style="margin-bottom: 0;">Notas de Débito</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón Estado de Cuenta -->
        <div class="row mt-3">
            <div class="col-12 text-center">
                <a href="{{ route('customer-statement.show', $cliente->CLIENTE) }}" class="btn btn-lg btn-primary">
                    <i class="fas fa-file-invoice"></i> Ver Estado de Cuenta Completo
                </a>
            </div>
        </div>
    </div>

    <!-- Modal de Facturas -->
    <div class="modal fade" id="facturasModal" tabindex="-1" role="dialog" aria-labelledby="facturasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="facturasModalLabel">
                        <i class="fas fa-file-invoice-dollar"></i> Facturas del Cliente
                    </h5>
                    <button type="button" class="close text-white" onclick="cerrarModalFacturas()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="facturas-loading" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-success"></i>
                        <p class="mt-3">Cargando facturas...</p>
                    </div>
                    <div id="facturas-content" style="display:none;">
                        <div class="table-responsive">
                            <table id="facturas-table" class="table table-bordered table-striped table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Factura</th>
                                        <th>NCF</th>
                                        <th>Fecha</th>
                                        <th class="text-right">Monto</th>
                                        <th class="text-right">Saldo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="facturas-tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="facturas-error" class="alert alert-danger" style="display:none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalFacturas()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalle de Factura -->
    <div class="modal fade" id="detalleFacturaModal" tabindex="-1" role="dialog" aria-labelledby="detalleFacturaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="detalleFacturaModalLabel">
                        <i class="fas fa-file-invoice"></i> Detalle de Factura
                    </h5>
                    <button type="button" class="close text-white" onclick="cerrarModalDetalle()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Encabezado de Factura -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-2">
                                    <strong>Factura:</strong> <span id="detalle-factura"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>NCF:</strong> <span id="detalle-ncf"></span>
                                </div>
                                <div class="col-md-2">
                                    <strong>Fecha:</strong> <span id="detalle-fecha"></span>
                                </div>
                                <div class="col-md-2">
                                    <strong>Monto:</strong> <span id="detalle-monto"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Saldo:</strong> <span id="detalle-saldo"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="detalle-loading" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-info"></i>
                        <p class="mt-3">Cargando detalle...</p>
                    </div>

                    <div id="detalle-content" style="display:none;">
                        <!-- Tabla para Facturas (FAC) -->
                        <div id="detalle-tabla-factura" class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Artículo</th>
                                        <th>Descripción</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Precio</th>
                                        <th class="text-right">Descuento %</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="detalle-tbody">
                                </tbody>
                                <tfoot class="font-weight-bold bg-light">
                                    <tr>
                                        <td colspan="5" class="text-right">TOTAL:</td>
                                        <td class="text-right" id="detalle-total">$0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Tabla para N/C y N/D -->
                        <div id="detalle-tabla-nc-nd" class="table-responsive" style="display:none;">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Documento</th>
                                        <th>Aplicado A</th>
                                        <th>NCF</th>
                                        <th>Fecha</th>
                                        <th class="text-right">Monto</th>
                                        <th>Asiento</th>
                                    </tr>
                                </thead>
                                <tbody id="detalle-tbody-nc-nd">
                                </tbody>
                                <tfoot class="font-weight-bold bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right">TOTAL:</td>
                                        <td class="text-right" id="detalle-total-nc-nd">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div id="detalle-error" class="alert alert-danger" style="display:none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalDetalle()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-imprimir-detalle" onclick="imprimirFacturaFormateada()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Aplicaciones de Pago -->
    <div class="modal fade" id="aplicacionesModal" tabindex="-1" role="dialog" aria-labelledby="aplicacionesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="aplicacionesModalLabel">
                        <i class="fas fa-money-check-alt"></i> Aplicaciones de Pago
                    </h5>
                    <button type="button" class="close text-white" onclick="cerrarModalAplicaciones()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Resumen de Factura -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-2">
                                    <strong>Factura:</strong> <span id="aplicacion-factura"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>NCF:</strong> <span id="aplicacion-ncf"></span>
                                </div>
                                <div class="col-md-2">
                                    <strong>Monto:</strong> <span id="aplicacion-monto"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Saldo:</strong> <span id="aplicacion-saldo"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="aplicaciones-loading" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Cargando aplicaciones...</p>
                    </div>
                    <div id="aplicaciones-content" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Documento Pago</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th class="text-right">Débito</th>
                                        <th class="text-right">Crédito</th>
                                        <th>Asiento</th>
                                    </tr>
                                </thead>
                                <tbody id="aplicaciones-tbody">
                                </tbody>
                                <tfoot class="font-weight-bold bg-light">
                                    <tr>
                                        <td colspan="3" class="text-right">TOTALES:</td>
                                        <td class="text-right" id="aplicaciones-total-debito">$0.00</td>
                                        <td class="text-right" id="aplicaciones-total-credito">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div id="aplicaciones-error" class="alert alert-danger" style="display:none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalAplicaciones()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="imprimirAplicaciones()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Documentos Pendientes -->
    <div class="modal fade" id="documentosPendientesModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-clock"></i> Documentos Pendientes
                    </h5>
                    <button type="button" class="close text-white" onclick="cerrarModalPendientes()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="pendientes-loading" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-warning"></i>
                        <p class="mt-3">Cargando documentos pendientes...</p>
                    </div>
                    <div id="pendientes-content" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Documento</th>
                                        <th>NCF</th>
                                        <th>Fecha</th>
                                        <th class="text-right">Monto</th>
                                        <th class="text-right">Saldo</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="pendientes-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="pendientes-error" class="alert alert-danger" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalPendientes()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Todos los Documentos -->
    <!-- Modal de Notas de Crédito -->
    <div class="modal fade" id="notasCreditoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-file-invoice"></i> Notas de Crédito
                    </h5>
                    <button type="button" class="close text-white" onclick="cerrarModalNC()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="nc-loading" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Cargando notas de crédito...</p>
                    </div>
                    <div id="nc-content" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Documento</th>
                                        <th>NCF</th>
                                        <th>Fecha</th>
                                        <th class="text-right">Monto</th>
                                        <th class="text-right">Saldo</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="nc-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="nc-error" class="alert alert-danger" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalNC()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Notas de Débito -->
    <div class="modal fade" id="notasDebitoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-file-invoice"></i> Notas de Débito
                    </h5>
                    <button type="button" class="close text-white" onclick="cerrarModalND()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="nd-loading" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-danger"></i>
                        <p class="mt-3">Cargando notas de débito...</p>
                    </div>
                    <div id="nd-content" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Documento</th>
                                        <th>NCF</th>
                                        <th>Fecha</th>
                                        <th class="text-right">Monto</th>
                                        <th class="text-right">Saldo</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="nd-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="nd-error" class="alert alert-danger" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalND()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box[style*="cursor:pointer"]:hover {
            opacity: 0.9;
            transform: scale(1.02);
            transition: all 0.3s ease;
        }
    </style>
@stop

@section('js')
<script>
// Datos del cliente desde PHP
const CLIENTE_CODE = @json($cliente->CLIENTE);
const ROUTES = {
    invoices: '{{ route("clients.invoices") }}',
    invoiceDetail: '{{ route("clients.invoice.detail") }}',
    invoicePayments: '{{ route("clients.invoice.payments") }}',
    printInvoice: '{{ route("clients.print.invoice") }}',
    pendingDocuments: '{{ route("clients.pending.documents") }}',
    creditNotes: '{{ route("clients.credit.notes") }}',
    debitNotes: '{{ route("clients.debit.notes") }}'
};

let currentFacturaDocumento = null;

console.log('=== Inicializando scripts de cliente ===');
console.log('Cliente Code:', CLIENTE_CODE);
console.log('jQuery disponible:', typeof $ !== 'undefined');
console.log('Rutas:', ROUTES);

// Definir funciones en el scope global usando window
window.verFacturasCliente = function() {
    const tipoMoneda = $('#tipo_moneda_detalle').val() || 'local';
    console.log('verFacturasCliente llamada - Cliente:', CLIENTE_CODE, 'Moneda:', tipoMoneda);

    // Preparar contenido del modal
    $('#facturas-loading').show();
    $('#facturas-content').hide();
    $('#facturas-error').hide();

    // Mostrar el modal manualmente (sin depender de Bootstrap JS)
    const modal = document.getElementById('facturasModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // Agregar backdrop
    if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }

    $.ajax({
        url: ROUTES.invoices,
        method: 'GET',
        data: { cliente: CLIENTE_CODE, moneda: tipoMoneda },
        success: function(response) {
            console.log('Respuesta de facturas:', response);
            $('#facturas-loading').hide();

            if (response.success && response.facturas.length > 0) {
                let html = '';
                const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

                response.facturas.forEach(function(factura) {
                    const monto = parseFloat(factura.MONTO || 0);
                    const saldo = parseFloat(factura.SALDO || 0);
                    const estadoBadge = saldo > 0 ? '<span class="badge badge-warning">Pendiente</span>' : '<span class="badge badge-success">Pagada</span>';

                    html += '<tr>';
                    html += '<td>' + factura.DOCUMENTO + '</td>';
                    html += '<td>' + (factura.NCF || 'Sin NCF') + '</td>';
                    html += '<td>' + new Date(factura.FECHA_DOCUMENTO).toLocaleDateString('es-DO') + '</td>';
                    html += '<td class="text-right">' + prefix + monto.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td class="text-right ' + (saldo > 0 ? 'text-danger font-weight-bold' : 'text-success') + '">' + prefix + saldo.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td>' + estadoBadge + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-info" onclick="verDetalleFactura(\'' + factura.DOCUMENTO + '\', \'' + (factura.NCF || 'Sin NCF') + '\', ' + monto + ', ' + saldo + ', \'FAC\')" title="Ver Detalle"><i class="fas fa-list"></i></button> ';
                    html += '<button class="btn btn-sm btn-primary" onclick="verAplicacionesFactura(\'' + factura.DOCUMENTO + '\', \'' + (factura.NCF || 'Sin NCF') + '\', ' + monto + ', ' + saldo + ', \'FAC\')" title="Ver Aplicaciones"><i class="fas fa-money-check-alt"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                });

                $('#facturas-tbody').html(html);
                $('#facturas-content').show();
            } else {
                $('#facturas-error').html('<i class="fas fa-info-circle"></i> No se encontraron facturas para este cliente.').show();
            }
        },
        error: function(xhr) {
            console.error('Error al cargar facturas:', xhr);
            $('#facturas-loading').hide();
            let errorMsg = 'Error al cargar facturas.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#facturas-error').html('<i class="fas fa-exclamation-triangle"></i> ' + errorMsg).show();
        }
    });
};

window.verDocumentosPendientes = function() {
    console.log('Abriendo modal de documentos pendientes...');
    const tipoMoneda = $('#tipo_moneda_detalle').val() || 'local';

    $('#pendientes-loading').show();
    $('#pendientes-content').hide();
    $('#pendientes-error').hide();

    const modal = document.getElementById('documentosPendientesModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }

    // Cargar documentos pendientes (SALDO > 0)
    $.ajax({
        url: ROUTES.pendingDocuments,
        method: 'GET',
        data: {
            cliente: CLIENTE_CODE,
            moneda: tipoMoneda
        },
        success: function(response) {
            $('#pendientes-loading').hide();
            let html = '';
            const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

            if (response.success && response.documentos && response.documentos.length > 0) {
                response.documentos.forEach(function(doc) {
                    const monto = tipoMoneda === 'dolar' ? (parseFloat(doc.MONTO_DOLAR) || 0) : (parseFloat(doc.MONTO_LOCAL) || 0);
                    const saldo = tipoMoneda === 'dolar' ? (parseFloat(doc.SALDO_DOLAR) || 0) : (parseFloat(doc.SALDO_LOCAL) || 0);

                    html += '<tr>';
                    html += '<td>' + doc.TIPO_NOMBRE + '</td>';
                    html += '<td>' + doc.DOCUMENTO + '</td>';
                    html += '<td>' + (doc.NCF || 'Sin NCF') + '</td>';
                    html += '<td>' + new Date(doc.FECHA_DOCUMENTO).toLocaleDateString('es-DO') + '</td>';
                    html += '<td class="text-right">' + prefix + monto.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td class="text-right text-danger font-weight-bold">' + prefix + saldo.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td class="text-center">';
                    html += '<div class="btn-group btn-group-sm">';
                    html += '<button class="btn btn-info" onclick="verDetalleFactura(\'' + doc.DOCUMENTO + '\', \'' + (doc.NCF || 'Sin NCF') + '\', ' + monto + ', ' + saldo + ', \'' + doc.TIPO + '\')" title="Ver Detalle"><i class="fas fa-list"></i></button>';
                    html += '<button class="btn btn-success" onclick="verAplicacionesFactura(\'' + doc.DOCUMENTO + '\', \'' + (doc.NCF || 'Sin NCF') + '\', ' + monto + ', ' + saldo + ', \'' + doc.TIPO + '\')" title="Ver Aplicaciones"><i class="fas fa-dollar-sign"></i></button>';
                    html += '</div>';
                    html += '</td>';
                    html += '</tr>';
                });
                $('#pendientes-tbody').html(html);
                $('#pendientes-content').show();
            } else {
                $('#pendientes-error').html('<i class="fas fa-info-circle"></i> No hay documentos pendientes.').show();
            }
        },
        error: function() {
            $('#pendientes-loading').hide();
            $('#pendientes-error').html('<i class="fas fa-exclamation-triangle"></i> Error al cargar documentos pendientes.').show();
        }
    });
};

window.verNotasCredito = function() {
    console.log('Abriendo modal de notas de crédito...');
    const tipoMoneda = $('#tipo_moneda_detalle').val() || 'local';

    $('#nc-loading').show();
    $('#nc-content').hide();
    $('#nc-error').hide();

    const modal = document.getElementById('notasCreditoModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }

    // Cargar notas de crédito
    $.ajax({
        url: ROUTES.creditNotes,
        method: 'GET',
        data: {
            cliente: CLIENTE_CODE,
            moneda: tipoMoneda
        },
        success: function(response) {
            $('#nc-loading').hide();
            let html = '';
            const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

            if (response.success && response.notasCredito && response.notasCredito.length > 0) {
                response.notasCredito.forEach(function(doc) {
                    const monto = tipoMoneda === 'dolar' ? (parseFloat(doc.MONTO_DOLAR) || 0) : (parseFloat(doc.MONTO_LOCAL) || 0);
                    const saldo = tipoMoneda === 'dolar' ? (parseFloat(doc.SALDO_DOLAR) || 0) : (parseFloat(doc.SALDO_LOCAL) || 0);

                    html += '<tr>';
                    html += '<td>' + doc.TIPO_NOMBRE + '</td>';
                    html += '<td>' + doc.DOCUMENTO + '</td>';
                    html += '<td>' + (doc.NCF || 'Sin NCF') + '</td>';
                    html += '<td>' + new Date(doc.FECHA_DOCUMENTO).toLocaleDateString('es-DO') + '</td>';
                    html += '<td class="text-right">' + prefix + monto.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td class="text-right">' + prefix + saldo.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td class="text-center">';
                    html += '<div class="btn-group btn-group-sm">';
                    html += '<button class="btn btn-info" onclick="verDetalleFactura(\'' + doc.DOCUMENTO + '\', \'' + (doc.NCF || 'Sin NCF') + '\', ' + monto + ', ' + saldo + ', \'' + doc.TIPO + '\')" title="Ver Detalle"><i class="fas fa-list"></i></button>';
                    html += '<button class="btn btn-success" onclick="verAplicacionesFactura(\'' + doc.DOCUMENTO + '\', \'' + (doc.NCF || 'Sin NCF') + '\', ' + monto + ', ' + saldo + ', \'' + doc.TIPO + '\')" title="Ver Aplicaciones"><i class="fas fa-dollar-sign"></i></button>';
                    html += '</div>';
                    html += '</td>';
                    html += '</tr>';
                });
                $('#nc-tbody').html(html);
                $('#nc-content').show();
            } else {
                $('#nc-error').html('<i class="fas fa-info-circle"></i> No hay notas de crédito.').show();
            }
        },
        error: function() {
            $('#nc-loading').hide();
            $('#nc-error').html('<i class="fas fa-exclamation-triangle"></i> Error al cargar notas de crédito.').show();
        }
    });
};

window.verNotasDebito = function() {
    console.log('Abriendo modal de notas de débito...');
    const tipoMoneda = $('#tipo_moneda_detalle').val() || 'local';

    $('#nd-loading').show();
    $('#nd-content').hide();
    $('#nd-error').hide();

    const modal = document.getElementById('notasDebitoModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }

    // Cargar notas de débito
    $.ajax({
        url: ROUTES.debitNotes,
        method: 'GET',
        data: {
            cliente: CLIENTE_CODE,
            moneda: tipoMoneda
        },
        success: function(response) {
            $('#nd-loading').hide();
            let html = '';
            const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

            if (response.success && response.notasDebito && response.notasDebito.length > 0) {
                response.notasDebito.forEach(function(doc) {
                    const monto = tipoMoneda === 'dolar' ? (parseFloat(doc.MONTO_DOLAR) || 0) : (parseFloat(doc.MONTO_LOCAL) || 0);
                    const saldo = tipoMoneda === 'dolar' ? (parseFloat(doc.SALDO_DOLAR) || 0) : (parseFloat(doc.SALDO_LOCAL) || 0);

                    html += '<tr>';
                    html += '<td>' + doc.TIPO_NOMBRE + '</td>';
                    html += '<td>' + doc.DOCUMENTO + '</td>';
                    html += '<td>' + (doc.NCF || 'Sin NCF') + '</td>';
                    html += '<td>' + new Date(doc.FECHA_DOCUMENTO).toLocaleDateString('es-DO') + '</td>';
                    html += '<td class="text-right">' + prefix + monto.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td class="text-right">' + prefix + saldo.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td class="text-center">';
                    html += '<div class="btn-group btn-group-sm">';
                    html += '<button class="btn btn-info" onclick="verDetalleFactura(\'' + doc.DOCUMENTO + '\', \'' + (doc.NCF || 'Sin NCF') + '\', ' + monto + ', ' + saldo + ', \'' + doc.TIPO + '\')" title="Ver Detalle"><i class="fas fa-list"></i></button>';
                    html += '<button class="btn btn-success" onclick="verAplicacionesFactura(\'' + doc.DOCUMENTO + '\', \'' + (doc.NCF || 'Sin NCF') + '\', ' + monto + ', ' + saldo + ', \'' + doc.TIPO + '\')" title="Ver Aplicaciones"><i class="fas fa-dollar-sign"></i></button>';
                    html += '</div>';
                    html += '</td>';
                    html += '</tr>';
                });
                $('#nd-tbody').html(html);
                $('#nd-content').show();
            } else {
                $('#nd-error').html('<i class="fas fa-info-circle"></i> No hay notas de débito.').show();
            }
        },
        error: function() {
            $('#nd-loading').hide();
            $('#nd-error').html('<i class="fas fa-exclamation-triangle"></i> Error al cargar notas de débito.').show();
        }
    });
};

window.cerrarModalPendientes = function() {
    const modal = document.getElementById('documentosPendientesModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
};

window.cerrarModalNC = function() {
    const modal = document.getElementById('notasCreditoModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
};

window.cerrarModalND = function() {
    const modal = document.getElementById('notasDebitoModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
};

window.verDetalleFactura = function(documento, ncf, monto, saldo, tipo) {
    const tipoMoneda = $('#tipo_moneda_detalle').val() || 'local';
    const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';
    const tipoDoc = tipo || 'FAC'; // Por defecto FAC si no se pasa
    console.log('verDetalleFactura llamada - Documento:', documento, 'Tipo:', tipoDoc);

    // Cerrar otros modales que puedan estar abiertos
    ['facturasModal', 'documentosPendientesModal', 'notasCreditoModal', 'notasDebitoModal'].forEach(function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    });

    // Guardar documento para imprimir
    currentFacturaDocumento = documento;

    $('#detalle-factura').text(documento);
    $('#detalle-ncf').text(ncf);
    $('#detalle-monto').text(prefix + monto.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
    $('#detalle-saldo').text(prefix + saldo.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));

    $('#detalle-loading').show();
    $('#detalle-content').hide();
    $('#detalle-error').hide();

    // Mostrar el modal manualmente
    const modal = document.getElementById('detalleFacturaModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // Asegurar que hay un backdrop
    if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }

    $.ajax({
        url: ROUTES.invoiceDetail,
        method: 'GET',
        data: {
            documento: documento,
            moneda: tipoMoneda,
            tipo: tipoDoc
        },
        success: function(response) {
            console.log('Respuesta detalle factura:', response);
            $('#detalle-loading').hide();

            if (response.success && response.lineas.length > 0) {
                let html = '';
                let total = 0;
                const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

                // Obtener fecha del documento
                let fecha = '';
                if (response.factura.FECHA) {
                    fecha = new Date(response.factura.FECHA).toLocaleDateString('es-DO');
                } else if (response.factura.FECHA_DOCUMENTO) {
                    fecha = new Date(response.factura.FECHA_DOCUMENTO).toLocaleDateString('es-DO');
                }
                $('#detalle-fecha').text(fecha);

                // Verificar si es N/C o N/D para usar tabla diferente
                if (tipoDoc === 'N/C' || tipoDoc === 'N/D') {
                    // Mostrar tabla de N/C y N/D, ocultar tabla de facturas
                    $('#detalle-tabla-factura').hide();
                    $('#detalle-tabla-nc-nd').show();

                    response.lineas.forEach(function(linea) {
                        const totalLinea = parseFloat(linea.TOTAL_LINEA || 0);
                        total += totalLinea;
                        const fechaLinea = linea.FECHA ? new Date(linea.FECHA).toLocaleDateString('es-DO') : '';

                        html += '<tr>';
                        html += '<td>' + linea.ARTICULO + '</td>';
                        html += '<td>' + (linea.DESCRIPCION || 'Sin descripción') + '</td>';
                        html += '<td>' + (linea.NCF || 'Sin NCF') + '</td>';
                        html += '<td>' + fechaLinea + '</td>';
                        html += '<td class="text-right font-weight-bold">' + prefix + totalLinea.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                        html += '<td>' + (linea.ASIENTO || 'N/A') + '</td>';
                        html += '</tr>';
                    });

                    $('#detalle-tbody-nc-nd').html(html);
                    $('#detalle-total-nc-nd').text(prefix + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                } else {
                    // Mostrar tabla de facturas, ocultar tabla de N/C y N/D
                    $('#detalle-tabla-factura').show();
                    $('#detalle-tabla-nc-nd').hide();

                    response.lineas.forEach(function(linea) {
                        const cantidad = parseFloat(linea.CANTIDAD || 0);
                        const precio = parseFloat(linea.PRECIO_UNITARIO || 0);
                        const descuento = parseFloat(linea.PORC_DESCUENTO || 0);
                        const totalLinea = parseFloat(linea.TOTAL_LINEA || 0);
                        total += totalLinea;

                        html += '<tr>';
                        html += '<td>' + linea.ARTICULO + '</td>';
                        html += '<td>' + (linea.DESCRIPCION || 'Sin descripción') + '</td>';
                        html += '<td class="text-right">' + cantidad.toFixed(2) + '</td>';
                        html += '<td class="text-right">' + prefix + precio.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                        html += '<td class="text-right">' + descuento.toFixed(2) + '%</td>';
                        html += '<td class="text-right font-weight-bold">' + prefix + totalLinea.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                        html += '</tr>';
                    });

                    $('#detalle-tbody').html(html);
                    $('#detalle-total').text(prefix + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                }

                $('#detalle-content').show();
            } else {
                $('#detalle-error').html('<i class="fas fa-info-circle"></i> No se encontró detalle para este documento.').show();
            }
        },
        error: function(xhr) {
            console.error('Error al cargar detalle:', xhr);
            $('#detalle-loading').hide();
            let errorMsg = 'Error al cargar detalle de factura.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#detalle-error').html('<i class="fas fa-exclamation-triangle"></i> ' + errorMsg).show();
        }
    });
};

window.verAplicacionesFactura = function(documento, ncf, monto, saldo, tipo) {
    const tipoMoneda = $('#tipo_moneda_detalle').val() || 'local';
    const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';
    const tipoDoc = tipo || 'FAC'; // Por defecto FAC si no se pasa
    console.log('verAplicacionesFactura llamada - Documento:', documento, 'Tipo:', tipoDoc);

    // Cerrar otros modales que puedan estar abiertos
    ['facturasModal', 'documentosPendientesModal', 'notasCreditoModal', 'notasDebitoModal'].forEach(function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    });

    $('#aplicacion-factura').text(documento);
    $('#aplicacion-ncf').text(ncf);
    $('#aplicacion-monto').text(prefix + monto.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
    $('#aplicacion-saldo').text(prefix + saldo.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));

    $('#aplicaciones-loading').show();
    $('#aplicaciones-content').hide();
    $('#aplicaciones-error').hide();

    // Mostrar el modal manualmente
    const modal = document.getElementById('aplicacionesModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // Asegurar que hay un backdrop
    if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }

    $.ajax({
        url: ROUTES.invoicePayments,
        method: 'GET',
        data: {
            documento: documento,
            cliente: CLIENTE_CODE,
            moneda: tipoMoneda,
            tipo: tipoDoc
        },
        success: function(response) {
            console.log('Respuesta aplicaciones:', response);
            $('#aplicaciones-loading').hide();

            if (response.success && response.aplicaciones.length > 0) {
                let html = '';
                let totalDebito = 0;
                let totalCredito = 0;
                const prefix = tipoMoneda === 'dolar' ? '$' : 'RD$';

                response.aplicaciones.forEach(function(aplicacion) {
                    const debito = parseFloat(aplicacion.DEBITO || 0);
                    const credito = parseFloat(aplicacion.CREDITO || 0);
                    totalDebito += debito;
                    totalCredito += credito;

                    html += '<tr>';
                    html += '<td>' + aplicacion.DOCUMENTO_PAGO + '</td>';
                    html += '<td>' + aplicacion.TIPO_NOMBRE + '</td>';
                    html += '<td>' + new Date(aplicacion.FECHA).toLocaleDateString('es-DO') + '</td>';
                    html += '<td class="text-right">' + prefix + debito.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td class="text-right">' + prefix + credito.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</td>';
                    html += '<td>' + (aplicacion.ASIENTO || 'N/A') + '</td>';
                    html += '</tr>';
                });

                $('#aplicaciones-tbody').html(html);
                $('#aplicaciones-total-debito').text(prefix + totalDebito.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                $('#aplicaciones-total-credito').text(prefix + totalCredito.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                $('#aplicaciones-content').show();
            } else {
                $('#aplicaciones-error').html('<i class="fas fa-info-circle"></i> No se encontraron aplicaciones de pago para esta factura.').show();
            }
        },
        error: function(xhr) {
            console.error('Error al cargar aplicaciones:', xhr);
            $('#aplicaciones-loading').hide();
            let errorMsg = 'Error al cargar aplicaciones de pago.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#aplicaciones-error').html('<i class="fas fa-exclamation-triangle"></i> ' + errorMsg).show();
        }
    });
};

// Funciones para cerrar modales
window.cerrarModalFacturas = function() {
    const modal = document.getElementById('facturasModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
};

window.cerrarModalDetalle = function() {
    const modal = document.getElementById('detalleFacturaModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
};

window.imprimirFacturaFormateada = function() {
    if (!currentFacturaDocumento) {
        alert('Error: No hay factura seleccionada');
        return;
    }

    console.log('=== ABRIENDO VISTA PREVIA DE FACTURA ===');
    console.log('Documento:', currentFacturaDocumento);

    // Construir URL para cargar el HTML de la factura
    const printUrl = ROUTES.printInvoice + '?documento=' + encodeURIComponent(currentFacturaDocumento) + '&cliente=' + encodeURIComponent(CLIENTE_CODE);
    console.log('URL:', printUrl);

    // Abrir directamente la URL en nueva ventana (como un link normal)
    const ventana = window.open(printUrl, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');

    if (!ventana) {
        alert('Por favor habilite las ventanas emergentes para ver la vista previa');
        return;
    }

    console.log('Ventana abierta correctamente');
};

window.cerrarModalAplicaciones = function() {
    const modal = document.getElementById('aplicacionesModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
};

window.imprimirAplicaciones = function() {
    const contenido = document.getElementById('aplicaciones-content').innerHTML;
    const ventana = window.open('', '_blank');
    ventana.document.write('<html><head><title>Aplicaciones de Pago</title>');
    ventana.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
    ventana.document.write('</head><body><div class="container mt-4"><h3>Aplicaciones de Pago</h3>' + contenido + '</div></body></html>');
    ventana.document.close();
    ventana.print();
};

// Document ready
$(document).ready(function() {
    console.log('Cliente Detail - Script cargado para cliente:', CLIENTE_CODE);
    console.log('Función verFacturasCliente disponible:', typeof window.verFacturasCliente);

    // Manejar cambio de moneda
    $('#tipo_moneda_detalle').on('change', function() {
        const tipoMoneda = $(this).val();
        console.log('Cambio de moneda a:', tipoMoneda);
        if (tipoMoneda === 'dolar') {
            $('.saldo-local').hide();
            $('.saldo-dolar').show();
        } else {
            $('.saldo-local').show();
            $('.saldo-dolar').hide();
        }
    });
});
</script>
@stop
