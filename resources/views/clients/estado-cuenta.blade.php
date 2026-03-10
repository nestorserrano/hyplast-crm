@extends('adminlte::page')

@section('template_title')
    Estado de Cuenta
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fa fa-file-invoice-dollar"></i> Estado de Cuenta - {{ $cliente->CLIENTE }} - {{ $cliente->NOMBRE }}</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="form-inline mb-4">
                        <div class="form-group mr-2">
                            <label for="fecha_inicio" class="mr-2">Desde</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                        </div>
                        <div class="form-group mr-2">
                            <label for="fecha_fin" class="mr-2">Hasta</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Documento</th>
                                    <th>Descripción</th>
                                    <th>Débito</th>
                                    <th>Crédito</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movimientos as $mov)
                                    <tr>
                                        <td>{{ $mov->FECHA }}</td>
                                        <td>{{ $mov->DOCUMENTO }}</td>
                                        <td>{{ $mov->DESCRIPCION }}</td>
                                        <td class="text-right">{{ number_format($mov->DEBITO, 2) }}</td>
                                        <td class="text-right">{{ number_format($mov->CREDITO, 2) }}</td>
                                        <td class="text-right">{{ number_format($mov->SALDO, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay movimientos para el período seleccionado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
