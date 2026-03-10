<!-- Encabezado del correo -->
<div class="mb-3">
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <h5 class="mb-2">
                @if($email->direction === 'sent')
                    <i class="fas fa-reply text-primary"></i> Enviado
                @else
                    <i class="fas fa-inbox text-success"></i> Recibido
                @endif
                @if($email->importance === 'high')
                    <i class="fas fa-exclamation-circle text-danger ml-2" title="Importancia Alta"></i>
                @endif
            </h5>
            <div class="text-muted small">
                <div class="row">
                    <div class="col-sm-2"><strong>De:</strong></div>
                    <div class="col-sm-10">{{ $email->from_name ?: $email->from_email }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-2"><strong>Para:</strong></div>
                    <div class="col-sm-10">{{ implode(', ', $email->to_emails) }}</div>
                </div>
                @if(!empty($email->cc_emails))
                <div class="row">
                    <div class="col-sm-2"><strong>CC:</strong></div>
                    <div class="col-sm-10">{{ implode(', ', $email->cc_emails) }}</div>
                </div>
                @endif
                <div class="row">
                    <div class="col-sm-2"><strong>Fecha:</strong></div>
                    <div class="col-sm-10">{{ $email->email_date->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
        <div class="text-right">
            @if(!$email->is_read && $email->direction === 'received')
                <span class="badge badge-primary">No leído</span>
            @endif
        </div>
    </div>
</div>

<hr>

<!-- Asunto -->
<h6 class="mb-3"><strong>Asunto:</strong> {{ $email->subject ?: '(Sin asunto)' }}</h6>

<!-- Cuerpo del mensaje -->
<div class="email-body mb-3" style="max-height: 400px; overflow-y: auto;">
    {!! $email->body_content !!}
</div>

<!-- Adjuntos -->
@if($email->has_attachments && $email->attachments->count() > 0)
    <hr>
    <div class="attachments mb-3">
        <h6><i class="fas fa-paperclip"></i> Adjuntos ({{ $email->attachments->count() }})</h6>
        <div class="list-group">
            @foreach($email->attachments as $attachment)
                <a href="{{ route('crm.leads.emails.attachment.download', [$lead->id, $attachment->id]) }}"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                   target="_blank">
                    <div>
                        <i class="fas fa-file"></i>
                        {{ $attachment->filename }}
                        <small class="text-muted">({{ $attachment->file_size_formatted }})</small>
                    </div>
                    <i class="fas fa-download"></i>
                </a>
            @endforeach
        </div>
    </div>
@endif

<!-- Acciones -->
<div class="mt-3">
    <button type="button" class="btn btn-primary btn-sm" onclick="replyToEmail({{ $email->id }})">
        <i class="fas fa-reply"></i> Responder
    </button>
    @if($email->direction === 'received' && !$email->is_read)
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="markAsRead({{ $email->id }})">
            <i class="far fa-envelope-open"></i> Marcar como leído
        </button>
    @endif
</div>

<!-- Conversación (si existe) -->
@if(isset($conversation) && $conversation->count() > 1)
    <hr>
    <div class="mt-4">
        <h6><i class="fas fa-comments"></i> Conversación ({{ $conversation->count() }} mensajes)</h6>
        <div class="timeline" style="max-height: 300px; overflow-y: auto;">
            @foreach($conversation as $msg)
                <div class="mb-3 p-2 {{ $msg->id == $email->id ? 'bg-light' : '' }}" style="border-left: 3px solid {{ $msg->direction === 'sent' ? '#007bff' : '#28a745' }};">
                    <div class="d-flex justify-content-between">
                        <strong class="small">
                            @if($msg->direction === 'sent')
                                <i class="fas fa-reply text-primary"></i>
                            @else
                                <i class="fas fa-inbox text-success"></i>
                            @endif
                            {{ $msg->from_name ?: $msg->from_email }}
                        </strong>
                        <span class="text-muted small">{{ $msg->email_date->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="text-muted small mt-1">
                        {{ $msg->body_preview ? (strlen($msg->body_preview) > 150 ? substr($msg->body_preview, 0, 150) . '...' : $msg->body_preview) : '(Sin contenido)' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<script>
function replyToEmail(emailId) {
    // Cerrar el modal de detalle
    $('#emailDetailModal').modal('hide');

    // Abrir modal de composición
    setTimeout(function() {
        $('#composeEmailModal').modal('show');
        // Aquí podrías pre-llenar datos del destinatario
    }, 300);
}

function markAsRead(emailId) {
    $.ajax({
        url: '/crm/leads/{{ $lead->id }}/emails/' + emailId + '/toggle-read',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function() {
            // Recargar el modal
            openEmailModal(emailId);
            // Recargar lista de correos
            if (typeof loadEmails === 'function') {
                loadEmails();
            }
        }
    });
}
</script>
