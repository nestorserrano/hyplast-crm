@extends('adminlte::page')

@section('title', 'Correo - ' . $email->subject)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-envelope"></i> {{ $email->subject ?: '(Sin asunto)' }}
        </h1>
        <div>
            <a href="{{ route('crm.leads.show', $lead->id) }}#emails" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Lead
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Mensaje principal -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">
                            @if($email->direction === 'sent')
                                <i class="fas fa-reply text-primary"></i> Enviado
                            @else
                                <i class="fas fa-inbox text-success"></i> Recibido
                            @endif
                            @if($email->importance === 'high')
                                <i class="fas fa-exclamation-circle text-danger ml-2" title="Importancia Alta"></i>
                            @endif
                        </h5>
                        <div class="text-muted">
                            <strong>De:</strong> {{ $email->from_name ?: $email->from_email }}<br>
                            <strong>Para:</strong> {{ implode(', ', $email->to_emails) }}
                            @if(!empty($email->cc_emails))
                                <br><strong>CC:</strong> {{ implode(', ', $email->cc_emails) }}
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <small class="text-muted">
                            {{ $email->email_date->format('d/m/Y H:i') }}
                        </small>
                        <br>
                        @if(!$email->is_read && $email->direction === 'received')
                            <span class="badge badge-primary">No leído</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Cuerpo del mensaje -->
                <div class="email-body">
                    {!! $email->body_content !!}
                </div>

                <!-- Adjuntos -->
                @if($email->has_attachments && $email->attachments->count() > 0)
                    <hr>
                    <div class="attachments">
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
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary btn-reply" data-email-id="{{ $email->id }}">
                    <i class="fas fa-reply"></i> Responder
                </button>
                @if($email->direction === 'received' && !$email->is_read)
                    <button type="button" class="btn btn-outline-secondary btn-mark-read" data-email-id="{{ $email->id }}">
                        <i class="far fa-envelope-open"></i> Marcar como leído
                    </button>
                @endif
            </div>
        </div>

        <!-- Conversación (si existe) -->
        @if($conversation->count() > 1)
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-comments"></i> Conversación ({{ $conversation->count() }} mensajes)</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($conversation as $msg)
                            @if($msg->id != $email->id)
                                <div class="time-label">
                                    <span class="bg-{{ $msg->direction === 'sent' ? 'primary' : 'success' }}">
                                        {{ $msg->email_date->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-envelope bg-{{ $msg->direction === 'sent' ? 'primary' : 'success' }}"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="far fa-clock"></i> {{ $msg->email_date->diffForHumans() }}
                                        </span>
                                        <h3 class="timeline-header">
                                            <strong>{{ $msg->from_name ?: $msg->from_email }}</strong>
                                            - {{ $msg->subject ?: '(Sin asunto)' }}
                                        </h3>
                                        <div class="timeline-body">
                                            {{ $msg->body_preview }}
                                        </div>
                                        <div class="timeline-footer">
                                            <a href="{{ route('crm.leads.emails.show', [$lead->id, $msg->id]) }}"
                                               class="btn btn-sm btn-primary">
                                                Ver completo
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar con info del lead -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user-tie"></i> Información del Lead</h5>
            </div>
            <div class="card-body">
                <dl>
                    @if($lead->company)
                        <dt>Empresa:</dt>
                        <dd>{{ $lead->company }}</dd>
                    @endif

                    <dt>Nombre:</dt>
                    <dd>{{ $lead->name }}</dd>

                    <dt>Email:</dt>
                    <dd><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></dd>

                    <dt>Teléfono:</dt>
                    <dd><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></dd>

                    @if($lead->leadStatus)
                        <dt>Estado:</dt>
                        <dd>
                            <span class="badge" style="background-color: {{ $lead->leadStatus->color }}">
                                {{ $lead->leadStatus->name }}
                            </span>
                        </dd>
                    @endif

                    @if($lead->assignedTo)
                        <dt>Asignado a:</dt>
                        <dd>{{ $lead->assignedTo->name }}</dd>
                    @endif
                </dl>

                <a href="{{ route('crm.leads.show', $lead->id) }}" class="btn btn-block btn-primary">
                    <i class="fas fa-eye"></i> Ver Lead Completo
                </a>
            </div>
        </div>

        <!-- Estadísticas de correos -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Estadísticas</h5>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Total de correos:</dt>
                    <dd>{{ $lead->emails()->count() }}</dd>

                    <dt>Enviados:</dt>
                    <dd>{{ $lead->emails()->where('direction', 'sent')->count() }}</dd>

                    <dt>Recibidos:</dt>
                    <dd>{{ $lead->emails()->where('direction', 'received')->count() }}</dd>

                    <dt>No leídos:</dt>
                    <dd>{{ $lead->emails()->where('is_read', false)->count() }}</dd>

                    <dt>Con adjuntos:</dt>
                    <dd>{{ $lead->emails()->where('has_attachments', true)->count() }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .email-body {
        font-size: 14px;
        line-height: 1.6;
        color: #333;
    }
    .email-body img {
        max-width: 100%;
        height: auto;
    }
    .attachments .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Marcar como leído
    $('.btn-mark-read').on('click', function() {
        const emailId = $(this).data('email-id');

        $.ajax({
            url: '{{ route("crm.leads.emails.toggleRead", [$lead->id, $email->id]) }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Marcado como leído',
                    timer: 1500,
                    showConfirmButton: false
                });
                $('.btn-mark-read').remove();
                $('.badge-primary').remove();
            }
        });
    });

    // Responder (redirigir al lead con modal abierto)
    $('.btn-reply').on('click', function() {
        window.location.href = '{{ route("crm.leads.show", $lead->id) }}#emails-reply';
    });
});
</script>
@stop
