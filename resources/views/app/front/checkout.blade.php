@extends('layouts.template')
@section('title', 'Confirmar Pedido')

@section('contenido')
<div class="container py-5">
    <h2 class="mb-4" style="font-family:'Cormorant Garamond',serif;color:#2C1810;">Confirmar tu Pedido</h2>

    <div class="row g-4">

        <div class="col-lg-7">
            <form action="{{ route('checkout.procesar') }}" method="POST">
                @csrf

                <div class="config-section mb-3">
                    <h5 class="config-step-title"><span class="config-num">1</span> Dirección de Envío</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Dirección completa</label>
                            <input id="direccion_envio" type="text" name="direccion_envio" class="form-control @error('direccion_envio') is-invalid @enderror"
                                   placeholder="Calle, número, barrio, referencia" value="{{ old('direccion_envio') }}" required>
                            @error('direccion_envio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Provincia</label>
                            <select id="ciudad_envio" name="ciudad_envio" class="form-select @error('ciudad_envio') is-invalid @enderror" required>
                                <option value="" disabled {{ old('ciudad_envio') ? '' : 'selected' }}>Selecciona tu provincia</option>
                                @foreach(config('comercial.provincias') as $provincia)
                                    <option value="{{ $provincia }}" {{ old('ciudad_envio') === $provincia ? 'selected' : '' }}>{{ $provincia }}</option>
                                @endforeach
                            </select>
                            @error('ciudad_envio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Teléfono de contacto para entrega</label>
                            <input type="tel" inputmode="tel" name="telefono_entrega" id="telefonoEntregaInput"
                                   class="form-control @error('telefono_entrega') is-invalid @enderror"
                                   pattern="[0-9+\-\s]{7,20}" minlength="7" maxlength="20"
                                   placeholder="Ej: 0991234567" value="{{ old('telefono_entrega') }}" required>
                            <div class="invalid-feedback" id="telefonoEntregaError">
                                @error('telefono_entrega')
                                    {{ $message }}
                                @else
                                    Ingresa un teléfono válido (7 a 20 dígitos, solo números, espacios, "+" o "-").
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="config-section mb-3">
                    <h5 class="config-step-title"><span class="config-num">2</span> Método de Pago</h5>
                    <p class="text-muted small mb-3">
                        El pago se confirma manualmente. Tras registrar tu pedido recibirás los datos
                        para completar el pago según el método elegido.
                    </p>
                    @foreach([
                        ['transferencia','🏦','Transferencia Bancaria'],
                        ['contraentrega','💵','Pago Contraentrega'],
                    ] as [$val, $emoji, $label])
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="pago_metodo" id="pm_{{ $val }}"
                               value="{{ $val }}" {{ old('pago_metodo') === $val ? 'checked' : ($loop->first ? 'checked' : '') }} required>
                        <label class="form-check-label" for="pm_{{ $val }}">{{ $emoji }} {{ $label }}</label>
                    </div>
                    @endforeach
                    @error('pago_metodo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="config-section mb-3">
                    <h5 class="config-step-title"><span class="config-num">3</span> Opciones de Regalo</h5>
                    @if(!empty($regaloConfig) && is_array($regaloConfig))
                        <div class="alert alert-success py-2 px-3 mb-3">
                            Configuración guardada correctamente para este pedido.
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3 small">
                            @php($nombresRegalo = ['blanco' => 'Bolsa de regalo blanca', 'negro' => 'Bolsa de regalo negra'])
                            <span class="badge bg-light text-dark border">🎁 {{ $nombresRegalo[$regaloConfig['presentacion'] ?? 'blanco'] ?? 'Bolsa de regalo blanca' }}</span>
                            @if(!empty($regaloConfig['ocasion']))
                                <span class="badge bg-light text-dark border">🎉 {{ $regaloConfig['ocasion'] }}</span>
                            @endif
                            <span class="badge bg-light text-dark border">🙈 {{ !empty($regaloConfig['anonimo']) ? 'Anónimo' : 'Con firma' }}</span>
                        </div>
                        <a href="{{ route('regalo', ['return_to' => 'checkout']) }}" class="btn btn-outline-dark btn-sm">
                            Editar configuración de regalo
                        </a>
                    @else
                        <p class="text-muted small mb-2">
                            ¿Este pedido es para regalo?
                        </p>
                        <p class="small text-muted mb-3">
                            Añade envoltura y dedicatoria en menos de un minuto.
                        </p>
                        <div class="d-grid d-sm-inline-flex">
                            <a href="{{ route('regalo', ['return_to' => 'checkout']) }}" class="btn btn-dark btn-sm">
                                Añadir opción de regalo
                            </a>
                        </div>
                    @endif
                </div>

                <div class="config-section">
                    <h5 class="config-step-title"><span class="config-num">4</span> Instrucciones de entrega (opcional)</h5>
                    <p class="text-muted small mb-2">
                        Usa este campo solo para referencias de entrega (edificio, piso, horario, punto de referencia).
                    </p>
                    <textarea name="notas" rows="3" maxlength="500" class="form-control"
                              placeholder="Ej: Entregar en portería, torre B, depto 203, de 14:00 a 18:00.">{{ old('notas') }}</textarea>
                    <small class="text-muted d-block mt-1">La dedicatoria del regalo se configura en “Opciones de Regalo”.</small>
                </div>

                <button type="submit" class="btn btn-gold btn-lg w-100 mt-4" id="btnConfirmarPedido">
                    Confirmar y Registrar Pedido
                </button>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="resumen-panel p-4 rounded-3" style="position:sticky;top:2rem;">
                <h5 class="mb-3">Resumen del Pedido</h5>

                @foreach($items as $item)
                <div class="d-flex justify-content-between align-items-start mb-2" style="font-size:.875rem;">
                    <div>
                        <div class="fw-600">{{ $item->fragancia->nombre }}</div>
                        <div class="text-muted small">
                            @if($item->tamano) · {{ $item->tamano->tamano }} @endif
                            · × {{ $item->cantidad }}
                        </div>
                        @if(!empty($item->regalo_presentacion))
                        @php($nombresRegaloItem = ['blanco' => 'Bolsa de regalo blanca', 'negro' => 'Bolsa de regalo negra'])
                        <div class="small text-success">
                            Envoltorio {{ $nombresRegaloItem[$item->regalo_presentacion] ?? ucfirst($item->regalo_presentacion) }} (sin costo)
                        </div>
                        @endif
                    </div>
                    <span class="fw-600">${{ number_format($item->tamano->precioFinal() * $item->cantidad, 2, ',', '.') }}</span>
                </div>
                @endforeach

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal sin IVA</span>
                    <span>${{ number_format($iva['base'], 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-muted small">
                    <span>IVA ({{ number_format($iva['porcentaje'], 2, ',', '.') }}%)</span>
                    <span>${{ number_format($iva['iva'], 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-muted small">
                    <span>Precio con IVA</span>
                    <span>${{ number_format($subtotalConIva, 2, ',', '.') }}</span>
                </div>

                @if($descuento > 0)
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Descuento</span>
                    <span>-${{ number_format($descuento, 2, ',', '.') }}</span>
                </div>
                @endif

                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Envío</span>
                    <span>{{ $envio == 0 ? 'Gratis' : '$' . number_format($envio, 2, ',', '.') }}</span>
                </div>
                @php($umbralEnvioGratis = (float) \App\Models\Configuracion::obtener('envio_gratis_desde', config('comercial.envio_gratis_desde', 80)))
                @if($envio > 0)
                <p class="small text-muted mb-2" style="margin-top:-.5rem;">
                    Se cobra envío porque tu compra es menor a ${{ number_format($umbralEnvioGratis, 0, ',', '.') }}.
                    Te faltan ${{ number_format($umbralEnvioGratis - ($subtotal - $descuento), 0, ',', '.') }} para envío gratis.
                </p>
                @endif

                @if(!empty($costoRegalo) && $costoRegalo > 0)
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Presentación de regalo</span>
                    <span>${{ number_format($costoRegalo, 2, ',', '.') }}</span>
                </div>
                @endif

                <p class="small text-muted mt-2 mb-2">{{ $iva['incluido'] ? 'El precio de las fragancias ya incluye IVA.' : 'Las fragancias no incluyen IVA.' }}</p>
                <hr>

                <div class="d-flex justify-content-between fw-700 fs-5">
                    <span>Total</span>
                    <span>${{ number_format($total, 2, ',', '.') }} USD</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('styles')
<style>
.config-section { background:#fff; border:1px solid #E8DDD5; border-radius:16px; padding:1.5rem; }
.config-step-title { font-size:1rem; color:#2C1810; font-weight:600; margin-bottom:1rem; display:flex; align-items:center; gap:.6rem; }
.config-num { background:#D4AF37; color:#2C1810; width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:700; flex-shrink:0; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const storageKey = 'checkoutFormState';
    const fieldNames = [
        'direccion_envio',
        'ciudad_envio',
        'telefono_entrega',
        'pago_metodo',
        'notas'
    ];

    function getFieldValue(field) {
        if (!field) {
            return '';
        }

        if (field.type === 'radio') {
            const checked = document.querySelector('[name="' + field.name + '"]:checked');
            return checked ? checked.value : '';
        }

        return field.value;
    }

    function saveFormState() {
        const data = {};
        fieldNames.forEach(name => {
            const field = document.querySelector('[name="' + name + '"]');
            data[name] = getFieldValue(field);
        });
        sessionStorage.setItem(storageKey, JSON.stringify(data));
    }

    function restoreFormState() {
        const saved = sessionStorage.getItem(storageKey);
        if (!saved) {
            return;
        }
        try {
            const data = JSON.parse(saved);
            fieldNames.forEach(name => {
                const field = document.querySelector('[name="' + name + '"]');
                if (!field || data[name] === undefined) {
                    return;
                }
                if (field.type === 'radio') {
                    const radio = document.querySelector('[name="' + name + '"][value="' + data[name] + '"]');
                    if (radio) {
                        radio.checked = true;
                    }
                } else {
                    field.value = data[name];
                }
            });
        } catch (e) {
            console.warn('No se pudo restaurar el estado del checkout:', e);
        }
    }

    fieldNames.forEach(name => {
        document.querySelectorAll('[name="' + name + '"]').forEach(field => {
            field.addEventListener('change', saveFormState);
            field.addEventListener('input', saveFormState);
        });
    });

    document.querySelectorAll('a[href^="/configurar-regalo"]').forEach(link => {
        link.addEventListener('click', saveFormState);
    });

    const checkoutForm = document.querySelector('form[action="{{ route('checkout.procesar') }}"]');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function () {
            sessionStorage.removeItem(storageKey);
            const btn = document.getElementById('btnConfirmarPedido');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registrando pedido...';
            }
        });
    }

    window.addEventListener('beforeunload', saveFormState);

    restoreFormState();

    // Validación en vivo del teléfono: mismo patrón que valida el backend
    // (7-20 caracteres, solo dígitos, espacios, "+" o "-"), para avisar
    // antes de enviar el formulario en vez de solo después del rechazo del servidor.
    const telefonoInput = document.getElementById('telefonoEntregaInput');
    const telefonoError = document.getElementById('telefonoEntregaError');
    const telefonoRegex = /^[0-9+\-\s]{7,20}$/;

    function validarTelefono() {
        if (!telefonoInput.value.trim()) {
            // Campo vacío: se deja que el "required" nativo del navegador se encargue.
            telefonoInput.classList.remove('is-invalid');
            return;
        }

        const esValido = telefonoRegex.test(telefonoInput.value.trim());
        telefonoInput.classList.toggle('is-invalid', !esValido);
        if (!esValido) {
            telefonoError.textContent = 'Ingresa un teléfono válido (7 a 20 dígitos, solo números, espacios, "+" o "-").';
        }
    }

    if (telefonoInput) {
        telefonoInput.addEventListener('input', validarTelefono);
        telefonoInput.addEventListener('blur', validarTelefono);
    }
});
</script>
@endsection
