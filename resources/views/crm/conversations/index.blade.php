@extends('adminlte::page')

@section('title', 'Conversaciones - CRM')

@section('content_header')
    <h1><i class="fab fa-whatsapp"></i> Conversaciones WhatsApp</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Lista de Conversaciones -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Todas las Conversaciones</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($conversations as $conv)
                        <div class="col-md-4 mb-3">
                            <div class="card {{ $conv->unreadCount() > 0 ? 'border-warning' : '' }}">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-user-circle"></i> {{ $conv->lead->name }}
                                        @if($conv->unreadCount() > 0)
                                        <span class="badge badge-danger">{{ $conv->unreadCount() }} nuevo(s)</span>
                                        @endif
                                    </h5>
                                    <p class="card-text small">
                                        <span class="badge badge-{{ $conv->lead->status->color }}">{{ $conv->lead->status->name }}</span><br>
                                        <i class="fas fa-phone"></i> {{ $conv->lead->phone }}<br>
                                        <i class="fas fa-clock"></i> {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : 'Sin actividad' }}
                                    </p>
                                    <div class="btn-group btn-block" role="group">
                                        <a href="{{ route('crm.conversations.show', $conv->id) }}"
                                           class="btn btn-primary btn-sm"
                                           style="flex: 1;">
                                            <i class="fas fa-comment"></i> Ver Chat
                                        </a>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $conv->lead->phone) }}"
                                           target="_blank"
                                           class="btn btn-success btn-sm"
                                           style="flex: 1;"
                                           title="Abrir en WhatsApp">
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No hay conversaciones activas
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Actualizar lista de conversaciones cada 10 segundos
    setInterval(function() {
        $.ajax({
            url: window.location.href,
            type: 'GET',
            success: function(html) {
                // Extraer solo el contenido de las conversaciones
                const tempDiv = $('<div>').html(html);
                const newContent = tempDiv.find('.card-body .row').html();

                if (newContent) {
                    // Actualizar contenido manteniendo el scroll
                    const scrollPos = $(window).scrollTop();
                    $('.card-body .row').html(newContent);
                    $(window).scrollTop(scrollPos);
                }
            },
            error: function(xhr) {
                console.error('Error al actualizar conversaciones:', xhr);
            }
        });
    }, 10000); // 10 segundos

    // Notificación de escritorio si hay mensajes nuevos
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Verificar nuevos mensajes no leídos
    let lastUnreadCount = {{ $conversations->sum(function($c) { return $c->unreadCount(); }) }};

    setInterval(function() {
        const currentUnreadCount = $('.badge-danger').length;

        if (currentUnreadCount > lastUnreadCount && 'Notification' in window && Notification.permission === 'granted') {
            new Notification('Nuevo mensaje de WhatsApp', {
                body: 'Tienes mensajes nuevos en tus conversaciones',
                icon: '/vendor/adminlte/dist/img/AdminLTELogo.png'
            });
        }

        lastUnreadCount = currentUnreadCount;
    }, 5000);
});
</script>
@stop
