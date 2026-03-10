<!-- Modal para Componer Correo -->
<div class="modal fade" id="composeEmailModal" tabindex="-1" role="dialog" aria-labelledby="composeEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="composeEmailForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="composeEmailModalLabel">
                        <i class="fas fa-envelope"></i> Nuevo Correo
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="lead_id" id="compose_lead_id" value="{{ $lead->id }}">

                    <!-- Destinatario (Solo lectura - Email del lead) -->
                    <div class="form-group">
                        <label for="compose_to">Para: <span class="text-danger">*</span></label>
                        <input type="email"
                               class="form-control"
                               id="compose_to"
                               name="to"
                               value="{{ $lead->email }}"
                               required
                               readonly
                               style="background-color: #f8f9fa; cursor: not-allowed;">
                        <small class="form-text text-muted">Destinatario vinculado al lead</small>
                    </div>

                    <!-- BCC oculto para gerencia (no visible para el usuario) -->
                    <input type="hidden" name="bcc" value="gerencia@hyplast.com.do">

                    <!-- CC (opcional) -->
                    <div class="form-group">
                        <label for="compose_cc">CC:</label>
                        <input type="text"
                               class="form-control"
                               id="compose_cc"
                               name="cc"
                               placeholder="cc@ejemplo.com (separar múltiples con coma)">
                        <small class="form-text text-muted">Opcional: separar múltiples direcciones con coma</small>
                    </div>

                    <!-- Asunto -->
                    <div class="form-group">
                        <label for="compose_subject">Asunto: <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="compose_subject"
                               name="subject"
                               required
                               placeholder="Asunto del correo">
                    </div>

                    <!-- Importancia -->
                    <div class="form-group">
                        <label for="compose_importance">Importancia:</label>
                        <select class="form-control" id="compose_importance" name="importance">
                            <option value="normal">Normal</option>
                            <option value="low">Baja</option>
                            <option value="high">Alta</option>
                        </select>
                    </div>

                    <!-- Cuerpo del mensaje -->
                    <div class="form-group">
                        <label for="compose_body">Mensaje: <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  id="compose_body"
                                  name="body"
                                  rows="10"
                                  required></textarea>
                    </div>

                    <!-- Adjuntos -->
                    <div class="form-group">
                        <label for="compose_attachments">Adjuntos:</label>
                        <div class="custom-file">
                            <input type="file"
                                   class="custom-file-input"
                                   id="compose_attachments"
                                   name="attachments[]"
                                   multiple>
                            <label class="custom-file-label" for="compose_attachments">Seleccionar archivos...</label>
                        </div>
                        <small class="form-text text-muted">Máximo 10 MB por archivo</small>
                        <div id="attachments_list" class="mt-2"></div>
                    </div>

                    <!-- Firma automática -->
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Enviado desde:</strong> {{ $microsoftToken->email ?? Auth::user()->email }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn_send_email">
                        <i class="fas fa-paper-plane"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
// Esperar a que todo esté cargado
$(function() {
    let tinyMceInitialized = false;

    // Inicializar TinyMCE solo cuando se abre el modal
    $('#composeEmailModal').on('shown.bs.modal', function () {
        // Actualizar token CSRF del formulario
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            const tokenInput = $('#composeEmailForm input[name="_token"]');
            if (tokenInput.length) {
                tokenInput.val(csrfMeta.getAttribute('content'));
            }
        }

        if (!tinyMceInitialized) {
            tinymce.init({
                selector: '#compose_body',
                base_url: '{{ asset("vendor/tinymce") }}',
                suffix: '.min',
                license_key: 'gpl',
                height: 300,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic underline strikethrough | forecolor backcolor | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist outdent indent | link | removeformat | help',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
                language: 'es',
                language_url: '{{ asset("vendor/tinymce/langs/es.js") }}',
                branding: false,
                promotion: false
            });
            tinyMceInitialized = true;
        }
    });

    // Mostrar nombres de archivos seleccionados
    $(document).on('change', '#compose_attachments', function() {
        const files = this.files;
        const label = $(this).next('.custom-file-label');
        const list = $('#attachments_list');

        if (files.length === 0) {
            label.text('Seleccionar archivos...');
            list.empty();
            return;
        }

        if (files.length === 1) {
            label.text(files[0].name);
        } else {
            label.text(files.length + ' archivos seleccionados');
        }

        // Mostrar lista de archivos
        let html = '<ul class="list-unstyled">';
        for (let i = 0; i < files.length; i++) {
            const size = (files[i].size / 1024 / 1024).toFixed(2);
            html += `<li><i class="fas fa-file"></i> ${files[i].name} (${size} MB)</li>`;
        }
        html += '</ul>';
        list.html(html);
    });

    // Enviar correo
    $(document).on('submit', '#composeEmailForm', function(e) {
        e.preventDefault();

        // Obtener contenido de TinyMCE
        let body = '';
        if (tinymce.get('compose_body')) {
            body = tinymce.get('compose_body').getContent();
        } else {
            body = $('#compose_body').val();
        }

        const formData = new FormData(this);
        formData.set('body', body);

        const btnSend = $('#btn_send_email');
        btnSend.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

        $.ajax({
            url: '{{ route("crm.leads.emails.send", $lead->id) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Correo Enviado!',
                    text: response.message,
                    timer: 3000,
                    showConfirmButton: false
                });

                $('#composeEmailModal').modal('hide');

                // Recargar pestaña de correos si está visible
                if (typeof loadEmails === 'function') {
                    setTimeout(function() {
                        loadEmails();
                    }, 500);
                }
            },
            error: function(xhr) {
                let message = 'Error al enviar el correo';
                let shouldReload = false;

                if (xhr.status === 419) {
                    // CSRF token mismatch - sesión expirada
                    message = 'Tu sesión ha expirado. Por favor, recarga la página para continuar.';
                    shouldReload = true;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('<br>');
                } else if (xhr.status === 401) {
                    message = 'Tu sesión de Microsoft ha expirado. Por favor, reconecta tu cuenta.';
                }

                Swal.fire({
                    icon: 'error',
                    title: shouldReload ? 'Sesión Expirada' : 'Error',
                    html: message,
                    confirmButtonText: shouldReload ? 'Recargar Página' : 'Entendido'
                }).then((result) => {
                    if (shouldReload && result.isConfirmed) {
                        location.reload();
                    }
                });
            },
            complete: function() {
                btnSend.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#composeEmailModal').on('hidden.bs.modal', function() {
        $('#composeEmailForm')[0].reset();
        if (tinymce.get('compose_body')) {
            tinymce.get('compose_body').setContent('');
        }
        $('#attachments_list').empty();
        $('.custom-file-label').text('Seleccionar archivos...');
    });
});
</script>
@endpush
