{{--
    Modal de confirmación reutilizable para acciones destructivas del panel admin.

    Reemplaza los confirm() nativos del navegador (feos, no se pueden estilizar,
    inconsistentes entre navegadores) por un modal Bootstrap con la misma
    identidad visual del resto del panel.

    Uso desde cualquier vista admin, en vez de:
        <button onclick="if(!confirm('¿Seguro?')) return; form.submit()">Eliminar</button>

    Se hace:
        <form id="miForm" action="..." method="POST">...</form>
        <button type="button" onclick="confirmarAccion({
            mensaje: '¿Eliminar la fragancia \'Chanel N5\'? Esta acción no se puede deshacer.',
            formId: 'miForm'
        })">Eliminar</button>

    O, si la acción no es un form sino un callback JS cualquiera:
        confirmarAccion({ mensaje: '...', onConfirmar: () => miFuncion() })

    El título y el texto del botón de confirmar son opcionales y tienen
    valores por defecto pensados para "eliminar".
--}}
<div class="modal fade" id="modalConfirmacionGlobal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmacionTitulo">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p id="modalConfirmacionMensaje" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="modalConfirmacionBtnConfirmar" class="btn btn-danger">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Namespace simple para no ensuciar el global scope más de lo necesario.
    window.__modalConfirmacion = (function () {
        let modalInstancia = null;
        let accionPendiente = null;

        function obtenerModal() {
            if (!modalInstancia) {
                const el = document.getElementById('modalConfirmacionGlobal');
                modalInstancia = new bootstrap.Modal(el);

                document.getElementById('modalConfirmacionBtnConfirmar').addEventListener('click', function () {
                    const accion = accionPendiente;
                    accionPendiente = null;
                    modalInstancia.hide();
                    if (typeof accion === 'function') {
                        accion();
                    }
                });
            }
            return modalInstancia;
        }

        /**
         * @param {Object} opciones
         * @param {string} opciones.mensaje - Texto de la pregunta de confirmación.
         * @param {string} [opciones.titulo] - Título del modal (por defecto "Confirmar acción").
         * @param {string} [opciones.textoBoton] - Texto del botón de confirmar (por defecto "Eliminar").
         * @param {string} [opciones.variante] - Color del botón: 'danger' (por defecto), 'warning', 'primary'.
         * @param {string} [opciones.formId] - Si se pasa, se envía (submit) ese <form> al confirmar.
         * @param {Function} [opciones.onConfirmar] - Alternativa a formId: callback a ejecutar al confirmar.
         */
        function confirmar(opciones) {
            document.getElementById('modalConfirmacionTitulo').textContent = opciones.titulo || 'Confirmar acción';
            document.getElementById('modalConfirmacionMensaje').textContent = opciones.mensaje || '¿Estás seguro?';

            const btn = document.getElementById('modalConfirmacionBtnConfirmar');
            btn.textContent = opciones.textoBoton || 'Eliminar';
            btn.className = 'btn btn-' + (opciones.variante || 'danger');

            accionPendiente = opciones.onConfirmar || function () {
                const form = opciones.formId ? document.getElementById(opciones.formId) : null;
                if (form) form.submit();
            };

            obtenerModal().show();
        }

        return { confirmar };
    })();

    // Fallback compatible para navegadores que no soportan requestSubmit() o
    // que lo ejecutan de forma inconsistente con modal/form submit.
    window.__submitFormSegura = function (formEl) {
        if (!formEl || !(formEl instanceof HTMLFormElement)) return;

        if (typeof formEl.requestSubmit === 'function') {
            try {
                formEl.requestSubmit();
                return;
            } catch (error) {
                // Fallback silencioso si el navegador bloquea la operación.
            }
        }

        formEl.submit();
    };

    // Alias corto para usar en los onclick de las vistas: confirmarAccion({...})
    function confirmarAccion(opciones) {
        window.__modalConfirmacion.confirmar(opciones);
    }
</script>
