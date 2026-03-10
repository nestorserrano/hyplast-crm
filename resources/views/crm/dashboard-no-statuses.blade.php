@extends('adminlte::page')

@section('title', trans('crm.dashboard.title'))

@section('content_header')
    <h1><i class="fas fa-chart-pie"></i> {{ trans('crm.dashboard.title') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Configuración Inicial Requerida
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Atención!</h5>
                        <p class="mb-0">
                            No se han encontrado <strong>estados de leads</strong> configurados para el conjunto actual
                            <strong>({{ session('conjunto_actual') }})</strong>.
                        </p>
                    </div>

                    <p class="lead">
                        El dashboard de CRM requiere que primero configure los estados de leads para poder visualizar
                        correctamente el pipeline y el tablero Kanban.
                    </p>

                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle"></i> ¿Qué son los estados de leads?</h5>
                        <p>
                            Los estados de leads representan las diferentes etapas del proceso de ventas, por ejemplo:
                        </p>
                        <ul>
                            <li><strong>Nuevo</strong> - Leads recién ingresados al sistema</li>
                            <li><strong>Contactado</strong> - Leads con los que ya se ha establecido comunicación</li>
                            <li><strong>Calificado</strong> - Leads que cumplen los requisitos para ser clientes</li>
                            <li><strong>Propuesta</strong> - Leads a los que se les ha enviado una propuesta</li>
                            <li><strong>Negociación</strong> - Leads en proceso de cierre</li>
                            <li><strong>Ganado</strong> - Leads convertidos en clientes</li>
                            <li><strong>Perdido</strong> - Leads que no se concretaron</li>
                        </ul>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('crm.lead-statuses.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus-circle"></i>
                            Ir a Configurar Estados de Leads
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="alert alert-light">
                        <h6><i class="fas fa-lightbulb"></i> Recomendación:</h6>
                        <p class="mb-0">
                            También puede ser necesario configurar las <strong>fuentes de leads</strong>
                            (por ejemplo: Referido, Sitio Web, Redes Sociales, etc.).
                            Puede hacerlo desde el menú
                            <a href="{{ route('crm.lead_sources.index') }}" class="alert-link">
                                Configuración → Fuentes de Leads
                            </a>.
                        </p>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        Una vez configurados los estados, el dashboard se mostrará automáticamente al recargar esta página.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .callout {
        border-left: 4px solid;
        border-radius: 0.25rem;
        padding: 1rem;
        margin: 1rem 0;
        background-color: #f8f9fa;
    }
    .callout-info {
        border-left-color: #17a2b8;
    }
    .card-warning {
        border-top: 3px solid #ffc107;
    }
</style>
@stop
