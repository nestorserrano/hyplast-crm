@extends('adminlte::page')

@section('title', 'Conversaciones - CRM')

@section('content_header')
    <h1><i class="fab fa-whatsapp"></i> Conversaciones WhatsApp</h1>
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
                            Para utilizar el módulo de conversaciones WhatsApp, primero necesita configurar
                            el sistema CRM para el conjunto actual <strong>({{ session('conjunto_actual') }})</strong>.
                        </p>
                    </div>

                    @if(!$hasStatuses)
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-times-circle"></i> Estados de Leads No Configurados</h5>
                        <p>
                            No se han encontrado estados de leads configurados. Los estados son necesarios
                            para clasificar y gestionar las conversaciones.
                        </p>
                        <a href="{{ route('crm.lead-statuses.index') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i>
                            Configurar Estados de Leads
                        </a>
                    </div>
                    @endif

                    @if(!$hasLeads)
                    <div class="callout callout-warning">
                        <h5><i class="fas fa-info-circle"></i> No Hay Leads Registrados</h5>
                        <p>
                            No existen leads registrados en el sistema. Las conversaciones de WhatsApp
                            están vinculadas a los leads (contactos/clientes potenciales).
                        </p>
                        <a href="{{ route('crm.leads.index') }}" class="btn btn-success">
                            <i class="fas fa-user-plus"></i>
                            Ir a Gestión de Leads
                        </a>
                    </div>
                    @endif

                    <div class="callout callout-info">
                        <h5><i class="fas fa-lightbulb"></i> ¿Cómo funcionan las conversaciones?</h5>
                        <p>
                            El módulo de conversaciones WhatsApp permite:
                        </p>
                        <ul>
                            <li>Visualizar conversaciones con leads/clientes</li>
                            <li>Enviar y recibir mensajes de WhatsApp</li>
                            <li>Mantener un historial de comunicaciones</li>
                            <li>Gestionar múltiples conversaciones simultáneamente</li>
                            <li>Marcar mensajes como leídos/no leídos</li>
                        </ul>
                        <p class="mb-0">
                            <strong>Nota:</strong> Las conversaciones se crean automáticamente cuando
                            se inicia comunicación con un lead, o pueden crearse manualmente desde
                            la ficha del lead.
                        </p>
                    </div>

                    <div class="text-center mt-4">
                        <h5>Pasos para comenzar:</h5>
                        <ol class="text-left" style="max-width: 600px; margin: 0 auto;">
                            <li class="mb-2">
                                <strong>Configurar Estados:</strong>
                                <a href="{{ route('crm.lead-statuses.index') }}">
                                    Ir a configuración de estados
                                </a>
                            </li>
                            <li class="mb-2">
                                <strong>Configurar Fuentes (opcional):</strong>
                                <a href="{{ route('crm.lead_sources.index') }}">
                                    Configurar fuentes de leads
                                </a>
                            </li>
                            <li class="mb-2">
                                <strong>Registrar Leads:</strong>
                                <a href="{{ route('crm.leads.index') }}">
                                    Agregar contactos/leads al sistema
                                </a>
                            </li>
                            <li class="mb-2">
                                <strong>Volver aquí:</strong>
                                Una vez configurado, las conversaciones estarán disponibles
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        Una vez completada la configuración, podrá gestionar conversaciones desde esta vista.
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
    .callout-warning {
        border-left-color: #ffc107;
    }
    .callout-danger {
        border-left-color: #dc3545;
    }
    .card-warning {
        border-top: 3px solid #ffc107;
    }
    ol {
        list-style-position: inside;
    }
</style>
@stop
