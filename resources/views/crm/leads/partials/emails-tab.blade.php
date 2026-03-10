@php
    $currentUser = Auth::user();
    $microsoftToken = $currentUser->microsoftToken()->first(); // Forzar carga de la relación
    $isMicrosoftConnected = $microsoftToken && !$microsoftToken->isExpired();
@endphp

<div class="tab-pane fade {{ request()->get('tab') == 'emails' ? 'show active' : '' }}" id="emails" role="tabpanel">
    @if(!$isMicrosoftConnected)
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> Cuenta de Microsoft no conectada</h5>
            <p>Para enviar y recibir correos vinculados a este lead, debes conectar tu cuenta de Microsoft 365.</p>
            <a href="{{ route('microsoft.connect') }}" class="btn btn-primary">
                <i class="fab fa-microsoft"></i> Conectar Microsoft 365
            </a>
        </div>
    @else
        <!-- Botones de acción -->
        <div class="mb-3 d-flex justify-content-between">
            <div>
                <button type="button" class="btn btn-primary" id="btn_new_email" data-toggle="modal" data-target="#composeEmailModal">
                    <i class="fas fa-envelope"></i> Nuevo Correo
                </button>
                <button type="button" class="btn btn-info" id="btn_sync_emails">
                    <i class="fas fa-sync"></i> Sincronizar
                </button>
            </div>
            <small class="text-muted align-self-center">
                Conectado como: <strong>{{ $microsoftToken->email }}</strong>
                <a href="#" id="disconnect_microsoft" class="text-danger ml-2" title="Desconectar">
                    <i class="fas fa-unlink"></i>
                </a>
            </small>
        </div>

        <!-- Lista de correos -->
        <div id="emails_container">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Cargando correos...</p>
            </div>
        </div>
    @endif
</div>

@push('js')
<script>
// Variable global para almacenar correos
let leadEmails = [];

// Función para obtener el token CSRF actualizado
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '{{ csrf_token() }}';
}

// Cargar correos cuando se activa la pestaña
$(document).ready(function() {
    // Manejar botón "Nuevo Correo" manualmente (fallback si Bootstrap no carga correctamente)
    $('#btn_new_email').on('click', function(e) {
        e.preventDefault();
        console.log('Abriendo modal de nuevo correo...');
        try {
            if (typeof $.fn.modal === 'function') {
                $('#composeEmailModal').modal('show');
            } else {
                console.error('Bootstrap modal no está disponible');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El sistema de modales no está cargado. Por favor, recarga la página.'
                });
            }
        } catch (error) {
            console.error('Error al abrir modal:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error al abrir modal',
                text: error.message
            });
        }
    });

    // Event listener delegado para abrir correos en modal (funciona con elementos dinámicos)
    $(document).on('click', '.email-item', function(e) {
        e.preventDefault();
        const emailId = $(this).data('email-id');
        if (emailId) {
            window.openEmailModal(emailId);
        }
    });

    // Cargar correos si la pestaña está activa al cargar
    @if($isMicrosoftConnected)
        if ($('#emails-tab').hasClass('active')) {
            console.log('Emails tab is active on load - loading emails...');
            loadEmails();
        }
    @endif

    $('#emails-tab').on('shown.bs.tab', function() {
        @if($isMicrosoftConnected)
            loadEmails();
        @endif
    });

    // Sincronizar correos
    $('#btn_sync_emails').on('click', function() {
        syncEmails();
    });

    // Desconectar Microsoft
    $('#disconnect_microsoft').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: '¿Desconectar Microsoft 365?',
            text: 'Se dejarán de sincronizar los correos',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, desconectar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("microsoft.disconnect") }}',
                    type: 'POST',
                    data: { _token: getCsrfToken() },
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Desconectado',
                            text: 'Cuenta de Microsoft desconectada',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            }
        });
    });
});

// Función para cargar correos
function loadEmails() {
    $('#emails_container').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Cargando correos...</p></div>');

    $.ajax({
        url: '{{ route("crm.leads.emails.index", $lead->id) }}',
        type: 'GET',
        success: function(response) {
            leadEmails = response.emails || [];
            renderEmails();
        },
        error: function(xhr) {
            $('#emails_container').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Error al cargar correos: ${xhr.responseJSON?.message || 'Error desconocido'}
                </div>
            `);
        }
    });
}

// Función para renderizar correos
function renderEmails() {
    if (leadEmails.length === 0) {
        $('#emails_container').html(`
            <div class="alert alert-primary" style="background-color: #cce5ff; border-color: #b8daff; color: #004085;">
                <i class="fas fa-info-circle"></i> No hay correos vinculados a este lead aún.
                <br><small>Los correos se sincronizarán automáticamente cuando envíes o recibas correos desde/hacia <strong>{{ $lead->email }}</strong></small>
            </div>
        `);
        return;
    }

    let html = '<div class="list-group">';

    leadEmails.forEach(email => {
        const isUnread = !email.is_read;
        const hasAttachments = email.has_attachments;
        const isSent = email.direction === 'sent';
        const importance = email.importance;

        const bgClass = isUnread ? 'bg-light' : '';
        const fontWeight = isUnread ? 'font-weight-bold' : '';

        html += `
            <a href="#" class="list-group-item list-group-item-action ${bgClass} email-item" data-email-id="${email.id}">
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                            ${isSent ? '<i class="fas fa-reply text-primary mr-2" title="Enviado"></i>' : '<i class="fas fa-inbox text-success mr-2" title="Recibido"></i>'}
                            <span class="${fontWeight}">${email.from_name || email.from_email}</span>
                            ${hasAttachments ? '<i class="fas fa-paperclip text-muted ml-2" title="Tiene adjuntos"></i>' : ''}
                            ${importance === 'high' ? '<i class="fas fa-exclamation-circle text-danger ml-2" title="Importancia alta"></i>' : ''}
                        </div>
                        <h6 class="mb-1 ${fontWeight}">${email.subject || '(Sin asunto)'}</h6>
                        <p class="mb-1 text-muted small">${email.body_preview || ''}</p>
                    </div>
                    <small class="text-muted text-nowrap ml-3">${formatEmailDate(email.email_date)}</small>
                </div>
            </a>
        `;
    });

    html += '</div>';

    $('#emails_container').html(html);
}

// Función global para abrir el modal de detalle del email
window.openEmailModal = function(emailId) {
    try {
        // Verificar que el modal exista
        if ($('#emailDetailModal').length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El modal de correos no está disponible. Por favor, recarga la página.'
            });
            return;
        }

        // Abrir modal
        $('#emailDetailModal').modal('show');

        // Mostrar loading
        $('#emailDetailContent').html(`
            <div class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Cargando correo...</p>
            </div>
        `);

        // Cargar contenido del email vía AJAX
        $.ajax({
            url: '{{ route("crm.leads.emails.show", ["lead" => $lead->id, "email" => "_EMAIL_ID_"]) }}'.replace('_EMAIL_ID_', emailId),
            type: 'GET',
            dataType: 'html',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#emailDetailContent').html(response);
            },
            error: function(xhr) {
                let errorMessage = 'Error al cargar el correo';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#emailDetailContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> ${errorMessage}
                    </div>
                `);
            }
        });
    } catch (error) {
        console.error('Error en openEmailModal:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo abrir el correo: ' + error.message
        });
    }
}

// Función para sincronizar correos
function syncEmails() {
    const btn = $('#btn_sync_emails');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sincronizando...');

    $.ajax({
        url: '{{ route("crm.leads.emails.sync", $lead->id) }}',
        type: 'POST',
        data: {
            _token: getCsrfToken(),
            limit: 50  // Reducir a 50 mensajes para mejor rendimiento
        },
        timeout: 60000,  // Timeout de 60 segundos
        success: function(response) {
            let message = response.message;
            let detailHtml = '';

            if (response.stats) {
                detailHtml = `
                    <div class="small text-left">
                        <div><strong>Nuevos:</strong> ${response.stats.created || 0}</div>
                        <div><strong>Actualizados:</strong> ${response.stats.updated || 0}</div>
                        <div><strong>Omitidos:</strong> ${response.stats.skipped || 0}</div>
                        ${response.stats.errors ? '<div class="text-danger"><strong>Errores:</strong> ' + response.stats.errors + '</div>' : ''}
                    </div>
                `;
            }

            Swal.fire({
                icon: 'success',
                title: 'Sincronización Completa',
                html: message + (detailHtml ? '<hr>' + detailHtml : ''),
                timer: 5000,
                showConfirmButton: true
            });

            loadEmails();
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Error al sincronizar correos';
            let errorDetail = '';
            let shouldReload = false;

            if (status === 'timeout') {
                errorMessage = 'La sincronización está tardando mucho';
                errorDetail = 'La operación continúa en segundo plano. Actualiza la página en unos momentos.';
            } else if (xhr.status === 419) {
                // CSRF token mismatch - sesión expirada
                errorMessage = 'Tu sesión ha expirado';
                errorDetail = 'Por favor, recarga la página para continuar.';
                shouldReload = true;
            } else if (xhr.responseJSON?.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 401) {
                errorMessage = 'Sesión de Microsoft expirada';
                errorDetail = 'Por favor, reconecta tu cuenta de Microsoft 365.';
            } else if (xhr.status === 500) {
                errorMessage = 'Error en el servidor';
                errorDetail = error || 'Verifica los logs del sistema.';
            }

            Swal.fire({
                icon: 'error',
                title: 'Error de Sincronización',
                html: errorMessage + (errorDetail ? '<br><small class="text-muted">' + errorDetail + '</small>' : ''),
                confirmButtonText: shouldReload ? 'Recargar página' : 'Entendido'
            }).then((result) => {
                if (shouldReload && result.isConfirmed) {
                    location.reload();
                } else {
                    // Recargar los correos existentes para no perder el filtro
                    loadEmails();
                }
            });
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Sincronizar');
        }
    });
}

// Formatear fecha de correo
function formatEmailDate(dateString) {
    if (!dateString) return '';

    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Hace un momento';
    if (diffMins < 60) return `Hace ${diffMins}min`;
    if (diffHours < 24) return `Hace ${diffHours}h`;
    if (diffDays < 7) return `Hace ${diffDays}d`;

    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
}
</script>
@endpush
