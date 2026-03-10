@extends('adminlte::page')

@section('title', 'Clientes - Test')

@section('content_header')
    <h1><i class="fas fa-users"></i> Test de Clientes</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Clientes de Softland (Modo Test)</h3>
                    </div>
                    <div class="card-body">
                        <div id="loading">
                            <i class="fas fa-spinner fa-spin"></i> Cargando clientes...
                        </div>
                        <div id="result" style="display:none;">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="clientes-body">
                                </tbody>
                            </table>
                        </div>
                        <div id="error" style="display:none;" class="alert alert-danger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    console.log('Iniciando carga de clientes...');

    $.ajax({
        url: "{{ route('clientes.data') }}",
        method: 'GET',
        success: function(response) {
            console.log('Respuesta recibida:', response);
            $('#loading').hide();
            $('#result').show();

            if (response.data && response.data.length > 0) {
                response.data.forEach(function(cliente) {
                    $('#clientes-body').append(
                        '<tr>' +
                            '<td>' + cliente.CLIENTE + '</td>' +
                            '<td>' + cliente.NOMBRE + '</td>' +
                            '<td>' + cliente.ACTIVO + '</td>' +
                        '</tr>'
                    );
                });
            } else {
                $('#error').html('No se encontraron clientes').show();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', xhr);
            $('#loading').hide();
            $('#error').html('Error al cargar clientes: ' + (xhr.responseJSON?.message || error)).show();
        }
    });
});
</script>
@stop
