@extends('adminlte::page')

@section('title', 'Conversaciones - CRM')

@section('content_header')
    <h1><i class="fab fa-whatsapp"></i> Conversaciones WhatsApp</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Lista de Conversaciones -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chats Activos</h3>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <div class="list-group list-group-flush">
                        @forelse($conversations as $conv)
                        <a href="{{ route('crm.conversations.show', $conv->id) }}"
                           class="list-group-item list-group-item-action {{ request()->route('id') == $conv->id ? 'active' : '' }}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fas fa-user-circle"></i> {{ $conv->lead->name }}
                                    @if($conv->unreadCount() > 0)
                                    <span class="badge badge-danger">{{ $conv->unreadCount() }}</span>
                                    @endif
                                </h6>
                                <small>{{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : 'Sin mensajes' }}</small>
                            </div>
                            <p class="mb-1 small text-truncate">
                                <span class="badge badge-{{ $conv->lead->status->color }}">{{ $conv->lead->status->name }}</span>
                                {{ $conv->lastMessage ? Str::limit($conv->lastMessage->content, 40) : 'No hay mensajes' }}
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-phone"></i> {{ $conv->lead->phone }}
                            </small>
                        </a>
                        @empty
                        <div class="list-group-item text-center text-muted">
                            No hay conversaciones activas
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Principal -->
        <div class="col-md-8">
            @if(isset($conversation))
            <div class="card direct-chat direct-chat-primary">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-1">
                                <i class="fab fa-whatsapp text-success"></i> {{ $conversation->lead->name }}
                            </h3>
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-phone"></i> {{ $conversation->lead->phone }}
                            </p>
                            @if($conversation->lead->company)
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-building"></i> {{ $conversation->lead->company }}
                            </p>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('crm.leads.show', $conversation->lead->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-user"></i> Ver Lead
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $conversation->lead->phone) }}"
                               target="_blank"
                               class="btn btn-sm btn-success"
                               title="Abrir en WhatsApp">
                                <i class="fab fa-whatsapp"></i> Abrir WhatsApp
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="height: 500px; overflow-y: auto;" id="chat-box">
                    <div class="direct-chat-messages">
                        @foreach($conversation->messages as $message)
                        <div class="direct-chat-msg {{ $message->is_from_lead ? '' : 'right' }}" data-message-id="{{ $message->id }}">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name {{ $message->is_from_lead ? 'float-left' : 'float-right' }}">
                                    {{ $message->is_from_lead ? $conversation->lead->name : ($message->user->name ?? 'Sistema') }}
                                </span>
                                <span class="direct-chat-timestamp {{ $message->is_from_lead ? 'float-right' : 'float-left' }}">
                                    {{ $message->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="direct-chat-text {{ $message->is_from_lead ? '' : 'bg-primary' }}">
                                {{ $message->content }}
                                @if($message->is_read && !$message->is_from_lead)
                                <span class="text-muted"><i class="fas fa-check-double"></i></span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer">
                    <form id="send-message-form">
                        <div class="input-group">
                            <input type="text" id="message-input" placeholder="Escribe un mensaje..." class="form-control" required>
                            <span class="input-group-append">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Enviar
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Selecciona una conversación para comenzar</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('js')
@if(isset($conversation))
<script>
$(document).ready(function() {
    const conversationId = {{ $conversation->id }};
    const chatBox = $('#chat-box');

    // Auto scroll al final
    chatBox.scrollTop(chatBox[0].scrollHeight);

    // Enviar mensaje
    $('#send-message-form').submit(function(e) {
        e.preventDefault();
        const content = $('#message-input').val().trim();

        if (!content) return;

        $.ajax({
            url: '{{ route("crm.conversations.send", $conversation->id) }}',
            type: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            data: {content: content, type: 'text'},
            success: function(response) {
                if (response.success) {
                    // Agregar mensaje al chat
                    appendMessage(response.message, false);
                    $('#message-input').val('');
                    chatBox.scrollTop(chatBox[0].scrollHeight);
                }
            },
            error: function(xhr) {
                console.error('Error al enviar mensaje:', xhr);
                let errorMsg = 'Error al enviar mensaje';

                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    errorMsg = 'No tienes permisos para enviar mensajes';
                } else if (xhr.status === 401) {
                    errorMsg = 'Sesión expirada. Por favor, inicia sesión nuevamente';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            }
        });
    });

    // Almacenar IDs de mensajes ya mostrados
    let displayedMessageIds = new Set();

    // Inicializar con mensajes actuales
    $('.direct-chat-msg').each(function() {
        const msgId = $(this).data('message-id');
        if (msgId) {
            displayedMessageIds.add(msgId);
        }
    });

    function appendMessage(message, isFromLead) {
        // Evitar duplicados
        if (displayedMessageIds.has(message.id)) {
            return;
        }

        displayedMessageIds.add(message.id);

        const userName = isFromLead
            ? '{{ $conversation->lead->name }}'
            : (message.user ? message.user.name : '{{ Auth::user()->name }}');

        const timestamp = message.created_at
            ? new Date(message.created_at).toLocaleString('es-DO', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
              })
            : 'Ahora';

        const messageHtml = `
            <div class="direct-chat-msg ${isFromLead ? '' : 'right'}" data-message-id="${message.id}">
                <div class="direct-chat-infos clearfix">
                    <span class="direct-chat-name ${isFromLead ? 'float-left' : 'float-right'}">
                        ${userName}
                    </span>
                    <span class="direct-chat-timestamp ${isFromLead ? 'float-right' : 'float-left'}">
                        ${timestamp}
                    </span>
                </div>
                <div class="direct-chat-text ${isFromLead ? '' : 'bg-primary'}">
                    ${message.content}
                    ${!isFromLead && message.is_read ? '<span class="text-muted"><i class="fas fa-check-double"></i></span>' : ''}
                </div>
            </div>
        `;
        $('.direct-chat-messages').append(messageHtml);

        // Auto scroll si el usuario está cerca del final
        const shouldScroll = chatBox.scrollTop() + chatBox.innerHeight() >= chatBox[0].scrollHeight - 100;
        if (shouldScroll) {
            chatBox.scrollTop(chatBox[0].scrollHeight);
        }

        // Notificación de sonido para mensajes nuevos del lead
        if (isFromLead) {
            playNotificationSound();
            showDesktopNotification(message.content);
        }
    }

    function playNotificationSound() {
        // Intentar reproducir sonido de notificación
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBiuEzu/TgjMHJnfE79yMOQgZZrfq6qNPDwU+mN7uw3IfBTCB0e/WiTYIHGG25+ydUBEKRqLh8L1vIgUwgM/z1oM0Bx51yO7hdkQMHms=');
            audio.play().catch(() => {});
        } catch (e) {}
    }

    function showDesktopNotification(messageContent) {
        // Mostrar notificación del navegador si está permitido
        if ('Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification('Nuevo mensaje de {{ $conversation->lead->name }}', {
                body: messageContent.substring(0, 100),
                icon: '/vendor/adminlte/dist/img/AdminLTELogo.png',
                tag: 'whatsapp-message-{{ $conversation->id }}',
                requireInteraction: false
            });

            // Auto cerrar después de 5 segundos
            setTimeout(() => notification.close(), 5000);

            // Opcional: hacer focus a la ventana al hacer clic
            notification.onclick = function() {
                window.focus();
                this.close();
            };
        }
    }

    // Polling para nuevos mensajes (cada 3 segundos)
    let isPolling = false;

    setInterval(function() {
        if (isPolling) return; // Evitar llamadas simultáneas

        isPolling = true;
        $.ajax({
            url: '{{ route("crm.conversations.messages", $conversation->id) }}',
            type: 'GET',
            success: function(messages) {
                // Procesar mensajes nuevos
                messages.forEach(function(message) {
                    if (!displayedMessageIds.has(message.id)) {
                        appendMessage(message, message.is_from_lead);
                    }
                });

                // Actualizar contador de no leídos en la sidebar
                updateUnreadCount();
            },
            error: function(xhr) {
                console.error('Error al obtener mensajes:', xhr);
            },
            complete: function() {
                isPolling = false;
            }
        });
    }, 3000);

    // Actualizar lista de conversaciones en sidebar cada 10 segundos
    setInterval(function() {
        $.ajax({
            url: window.location.href,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                const tempDiv = $('<div>').html(html);
                const newSidebar = tempDiv.find('.col-md-4 .list-group').html();

                if (newSidebar) {
                    // Actualizar sidebar manteniendo scroll
                    const sidebarScroll = $('.col-md-4 .card-body').scrollTop();
                    $('.col-md-4 .list-group').html(newSidebar);
                    $('.col-md-4 .card-body').scrollTop(sidebarScroll);
                }
            },
            error: function(xhr) {
                console.error('Error al actualizar sidebar:', xhr);
            }
        });
    }, 10000);

    // Notificaciones de escritorio para nuevos mensajes
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    function updateUnreadCount() {
        // Actualizar el badge de mensajes no leídos en la lista
        $.ajax({
            url: '{{ route("crm.conversations.show", $conversation->id) }}',
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                // Extraer solo el contador de mensajes no leídos
                const tempDiv = $('<div>').html(html);
                const unreadBadge = tempDiv.find('.list-group-item.active .badge-danger').text();

                // Actualizar badge en la conversación actual
                const currentConvBadge = $('.list-group-item.active .badge-danger');
                if (unreadBadge && parseInt(unreadBadge) > 0) {
                    if (currentConvBadge.length) {
                        currentConvBadge.text(unreadBadge);
                    }
                } else {
                    currentConvBadge.remove();
                }
            }
        });
    }
});
</script>
@endif
@stop

@section('css')
<style>
.direct-chat-messages {
    height: 450px;
}
.direct-chat-text {
    border-radius: 10px;
    padding: 10px 15px;
}
.direct-chat-msg.right .direct-chat-text {
    margin-right: 10px;
}
.list-group-item.active {
    background-color: #007bff !important;
    border-color: #007bff !important;
}
</style>
@stop
