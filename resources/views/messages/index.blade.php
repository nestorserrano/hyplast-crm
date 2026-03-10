@extends('adminlte::page')

@section('title', 'Mensajes')

@section('content_header')
    <h1>
        <i class="fas fa-comments"></i> Mensajes
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Mis Conversaciones</h3>
                    <div class="card-tools">
                        <a href="{{ route('messages.new') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Mensaje
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($conversations->isEmpty())
                        <div class="text-center p-4">
                            <p class="text-muted">No tienes conversaciones aún</p>
                            <a href="{{ route('messages.new') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Iniciar conversación
                            </a>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($conversations as $conversation)
                                <li class="list-group-item">
                                    <a href="{{ route('messages.show', $conversation['id']) }}" class="d-flex align-items-center text-decoration-none">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1">
                                                {{ $conversation['other_user']['name'] }}
                                                @if($conversation['unread_count'] > 0)
                                                    <span class="badge badge-danger">{{ $conversation['unread_count'] }}</span>
                                                @endif
                                            </h5>
                                            @if($conversation['last_message'])
                                                <p class="mb-1 text-muted">
                                                    {{ Str::limit($conversation['last_message']->message, 50) }}
                                                </p>
                                                <small class="text-muted">{{ $conversation['last_message']->time_ago }}</small>
                                            @endif
                                        </div>
                                        <div>
                                            <i class="fas fa-chevron-right"></i>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    // Actualizar notificaciones cada 30 segundos
    setInterval(function() {
        location.reload();
    }, 30000);
</script>
@stop
