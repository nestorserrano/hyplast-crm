@extends('adminlte::page')

@section('title', 'Chat - ' . $otherUser->name)

@section('content_header')
    <h1>
        <i class="fas fa-comment"></i> Chat con {{ $otherUser->name }}
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline direct-chat direct-chat-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-circle"></i> {{ $otherUser->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('messages.index') }}" class="btn btn-tool">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="direct-chat-messages" id="chat-messages" style="height: 450px; overflow-y: auto;">
                        @foreach($messages as $message)
                            @if($message->from == Auth::id())
                                <!-- Mensaje enviado -->
                                <div class="direct-chat-msg right">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-right">Tú</span>
                                        <span class="direct-chat-timestamp float-left">{{ $message->time_ago }}</span>
                                    </div>
                                    <div class="direct-chat-text">
                                        @if($message->message_type === 'text')
                                            {!! nl2br(e($message->message)) !!}
                                        @elseif($message->message_type === 'image')
                                            <div class="mb-2">
                                                <a href="{{ $message->attachment_url }}" target="_blank">
                                                    <img src="{{ $message->attachment_url }}" class="img-fluid" style="max-width: 300px; border-radius: 8px;">
                                                </a>
                                            </div>
                                            @if($message->message)
                                                <div class="mt-2">{!! nl2br(e($message->message)) !!}</div>
                                            @endif
                                        @elseif($message->message_type === 'audio')
                                            <div class="mb-2">
                                                <audio controls style="max-width: 100%;">
                                                    <source src="{{ $message->attachment_url }}" type="{{ $message->attachment_mime }}">
                                                </audio>
                                            </div>
                                            @if($message->message)
                                                <div class="mt-2">{!! nl2br(e($message->message)) !!}</div>
                                            @endif
                                        @elseif($message->message_type === 'video')
                                            <div class="mb-2">
                                                <video controls style="max-width: 300px; border-radius: 8px;">
                                                    <source src="{{ $message->attachment_url }}" type="{{ $message->attachment_mime }}">
                                                </video>
                                            </div>
                                            @if($message->message)
                                                <div class="mt-2">{!! nl2br(e($message->message)) !!}</div>
                                            @endif
                                        @elseif($message->message_type === 'file')
                                            <div class="mb-2">
                                                <a href="{{ $message->attachment_url }}" target="_blank" class="btn btn-sm btn-light">
                                                    <i class="fas fa-file"></i> {{ $message->attachment_name }}
                                                    <small>({{ $message->getAttachmentSizeFormatted() }})</small>
                                                </a>
                                            </div>
                                            @if($message->message)
                                                <div class="mt-2">{!! nl2br(e($message->message)) !!}</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @else
                                <!-- Mensaje recibido -->
                                <div class="direct-chat-msg">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-left">{{ $message->sender->name }}</span>
                                        <span class="direct-chat-timestamp float-right">{{ $message->time_ago }}</span>
                                    </div>
                                    <div class="direct-chat-text">
                                        @if($message->message_type === 'text')
                                            {!! nl2br(e($message->message)) !!}
                                        @elseif($message->message_type === 'image')
                                            <div class="mb-2">
                                                <a href="{{ $message->attachment_url }}" target="_blank">
                                                    <img src="{{ $message->attachment_url }}" class="img-fluid" style="max-width: 300px; border-radius: 8px;">
                                                </a>
                                            </div>
                                            @if($message->message)
                                                <div class="mt-2">{!! nl2br(e($message->message)) !!}</div>
                                            @endif
                                        @elseif($message->message_type === 'audio')
                                            <div class="mb-2">
                                                <audio controls style="max-width: 100%;">
                                                    <source src="{{ $message->attachment_url }}" type="{{ $message->attachment_mime }}">
                                                </audio>
                                            </div>
                                            @if($message->message)
                                                <div class="mt-2">{!! nl2br(e($message->message)) !!}</div>
                                            @endif
                                        @elseif($message->message_type === 'video')
                                            <div class="mb-2">
                                                <video controls style="max-width: 300px; border-radius: 8px;">
                                                    <source src="{{ $message->attachment_url }}" type="{{ $message->attachment_mime }}">
                                                </video>
                                            </div>
                                            @if($message->message)
                                                <div class="mt-2">{!! nl2br(e($message->message)) !!}</div>
                                            @endif
                                        @elseif($message->message_type === 'file')
                                            <div class="mb-2">
                                                <a href="{{ $message->attachment_url }}" target="_blank" class="btn btn-sm btn-light">
                                                    <i class="fas fa-file"></i> {{ $message->attachment_name }}
                                                    <small>({{ $message->getAttachmentSizeFormatted() }})</small>
                                                </a>
                                            </div>
                                            @if($message->message)
                                                <div class="mt-2">{!! nl2br(e($message->message)) !!}</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="card-footer">
                    <form id="message-form" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div id="summernote"></div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-8">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary" id="attach-image-btn" title="Enviar imagen">
                                        <i class="fas fa-image"></i> Imagen
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="attach-audio-btn" title="Enviar audio">
                                        <i class="fas fa-microphone"></i> Audio
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="attach-video-btn" title="Enviar video">
                                        <i class="fas fa-video"></i> Video
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="attach-file-btn" title="Enviar archivo">
                                        <i class="fas fa-paperclip"></i> Archivo
                                    </button>
                                </div>
                                <input type="file" id="attachment-input" name="attachment" style="display: none;" accept="">
                                <div id="file-preview" class="mt-2"></div>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Enviar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
<style>
    .direct-chat-msg.right .direct-chat-text {
        background-color: #007bff;
        color: white;
        margin-right: 10px;
        border-radius: 10px;
        padding: 10px 15px;
    }

    .direct-chat-msg .direct-chat-text {
        background-color: #f4f4f4;
        border-radius: 10px;
        padding: 10px 15px;
    }

    .direct-chat-messages {
        padding: 15px;
    }

    .direct-chat-msg {
        margin-bottom: 15px;
    }

    #file-preview {
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 5px;
        display: none;
        margin-top: 10px;
    }

    #file-preview.active {
        display: block;
    }

    .file-preview-item {
        display: flex;
        align-items: center;
        padding: 8px;
        background: white;
        border-radius: 5px;
    }

    .file-preview-item img {
        max-width: 150px;
        max-height: 150px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 10px;
    }

    .file-preview-item i {
        margin-right: 10px;
        font-size: 24px;
    }

    .file-preview-item .btn-remove {
        margin-left: auto;
    }

    .note-editor {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }

    .note-editable {
        min-height: 100px;
    }
</style>
@stop

@section('js')
<script>
// Función para cargar Summernote y luego inicializar
function loadSummernoteAndInit() {
    const $ = jQuery;

    // Si Summernote ya está cargado, inicializar directamente
    if (typeof $.fn.summernote !== 'undefined') {
        console.log('Summernote ya está cargado');
        initMessagePage();
        return;
    }

    // Cargar Summernote dinámicamente
    console.log('Cargando Summernote...');
    $.getScript('https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js')
        .done(function() {
            console.log('Summernote cargado exitosamente');
            initMessagePage();
        })
        .fail(function(jqxhr, settings, exception) {
            console.error('Error al cargar Summernote:', exception);
            alert('Error al cargar el editor de texto. Por favor, recarga la página.');
        });
}

function initMessagePage() {
    const $ = jQuery;
    const conversationId = {{ $conversation->id }};
    const userId = {{ Auth::id() }};
    let selectedFile = null;

    // Inicializar Summernote
    $('#summernote').summernote({
        placeholder: 'Escribe tu mensaje aquí...',
        tabsize: 2,
        height: 120,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
        ],
        callbacks: {
            onKeydown: function(e) {
                // Enviar con Ctrl+Enter
                if (e.ctrlKey && e.keyCode === 13) {
                    $('#message-form').submit();
                }
            }
        }
    });

    // Scroll al final del chat
    function scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    scrollToBottom();

    // Botones de adjuntar
    $('#attach-image-btn').click(function() {
        $('#attachment-input').attr('accept', 'image/*').click();
    });

    $('#attach-audio-btn').click(function() {
        $('#attachment-input').attr('accept', 'audio/*').click();
    });

    $('#attach-video-btn').click(function() {
        $('#attachment-input').attr('accept', 'video/*').click();
    });

    $('#attach-file-btn').click(function() {
        $('#attachment-input').attr('accept', '*').click();
    });

    // Preview de archivo seleccionado con imagen
    $('#attachment-input').change(function() {
        const file = this.files[0];
        if (file) {
            selectedFile = file;
            let icon = 'fa-file';
            let previewHtml = '';

            if (file.type.startsWith('image/')) {
                icon = 'fa-image';
                // Crear preview de imagen
                const reader = new FileReader();
                reader.onload = function(e) {
                    const sizeKB = (file.size / 1024).toFixed(2);
                    $('#file-preview').addClass('active').html(`
                        <div class="file-preview-item">
                            <img src="${e.target.result}" alt="Preview">
                            <div class="flex-grow-1">
                                <strong>${file.name}</strong><br>
                                <small class="text-muted">${sizeKB} KB</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger btn-remove" id="remove-file-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            } else {
                if (file.type.startsWith('audio/')) icon = 'fa-microphone';
                else if (file.type.startsWith('video/')) icon = 'fa-video';

                const sizeKB = (file.size / 1024).toFixed(2);
                $('#file-preview').addClass('active').html(`
                    <div class="file-preview-item">
                        <i class="fas ${icon} text-primary"></i>
                        <div class="flex-grow-1">
                            <strong>${file.name}</strong><br>
                            <small class="text-muted">${sizeKB} KB</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger btn-remove" id="remove-file-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);
            }
        }
    });

    // Remover archivo
    $(document).on('click', '#remove-file-btn', function() {
        $('#attachment-input').val('');
        selectedFile = null;
        $('#file-preview').removeClass('active').html('');
    });

    // Enviar mensaje
    $('#message-form').on('submit', function(e) {
        e.preventDefault();

        const message = $('#summernote').summernote('code').trim();
        const formData = new FormData();

        formData.append('_token', '{{ csrf_token() }}');
        formData.append('message', message);

        if (selectedFile) {
            formData.append('attachment', selectedFile);
        }

        // Validar que haya mensaje o archivo
        if (message === '<p><br></p>' && !selectedFile) {
            alert('Debe escribir un mensaje o adjuntar un archivo');
            return;
        }

        $.ajax({
            url: '{{ route("messages.send", $conversation->id) }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#summernote').summernote('code', '');
                    $('#attachment-input').val('');
                    selectedFile = null;
                    $('#file-preview').removeClass('active').html('');
                    loadMessages();
                }
            },
            error: function(xhr) {
                alert('Error al enviar mensaje: ' + (xhr.responseJSON?.error || 'Error desconocido'));
            }
        });
    });

    // Cargar mensajes
    function loadMessages() {
        $.ajax({
            url: '{{ route("messages.get-messages", $conversation->id) }}',
            method: 'GET',
            success: function(messages) {
                let html = '';
                messages.forEach(function(message) {
                    const isOwn = message.from == userId;
                    const alignment = isOwn ? 'right' : '';
                    const nameAlign = isOwn ? 'float-right' : 'float-left';
                    const timeAlign = isOwn ? 'float-left' : 'float-right';
                    const name = isOwn ? 'Tú' : message.sender.name;

                    let content = '';
                    if (message.message_type === 'text') {
                        content = message.message;
                    } else if (message.message_type === 'image') {
                        content = `<div class="mb-2"><a href="${message.attachment_url}" target="_blank"><img src="${message.attachment_url}" class="img-fluid" style="max-width: 300px; border-radius: 8px;"></a></div>`;
                        if (message.message) content += `<div class="mt-2">${message.message}</div>`;
                    } else if (message.message_type === 'audio') {
                        content = `<div class="mb-2"><audio controls style="max-width: 100%;"><source src="${message.attachment_url}" type="${message.attachment_mime}"></audio></div>`;
                        if (message.message) content += `<div class="mt-2">${message.message}</div>`;
                    } else if (message.message_type === 'video') {
                        content = `<div class="mb-2"><video controls style="max-width: 300px; border-radius: 8px;"><source src="${message.attachment_url}" type="${message.attachment_mime}"></video></div>`;
                        if (message.message) content += `<div class="mt-2">${message.message}</div>`;
                    } else if (message.message_type === 'file') {
                        content = `<div class="mb-2"><a href="${message.attachment_url}" target="_blank" class="btn btn-sm btn-light"><i class="fas fa-file"></i> ${message.attachment_name}</a></div>`;
                        if (message.message) content += `<div class="mt-2">${message.message}</div>`;
                    }

                    html += `
                        <div class="direct-chat-msg ${alignment}">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name ${nameAlign}">${name}</span>
                                <span class="direct-chat-timestamp ${timeAlign}">${message.time_ago}</span>
                            </div>
                            <div class="direct-chat-text">
                                ${content}
                            </div>
                        </div>
                    `;
                });

                $('#chat-messages').html(html);
                scrollToBottom();
            }
        });
    }

    // Actualizar mensajes cada 5 segundos
    setInterval(loadMessages, 5000);
}

// Inicializar cuando jQuery esté listo
$(document).ready(function() {
    console.log('DOM listo, iniciando carga de Summernote...');
    loadSummernoteAndInit();
});
