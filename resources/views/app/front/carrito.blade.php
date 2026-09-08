@extends('layouts.template')
@section('title', 'Mi Carrito')

@section('contenido')
<div class="container py-5">
    <h2 class="mb-4">Mi Carrito</h2>

    @if($items->count())
    <div class="row g-4">
        <div class="col-lg-8">
            @foreach($items as $item)
            <div class="carrito-item d-flex gap-3 align-items-start">
                <div class="carrito-item-img">
                    <img src="{{ $item->fragancia->imagenMostrar() }}"
                         alt="{{ $item->fragancia->nombre }}" loading="lazy"
                         onerror="this.onerror=null;this.src='{{ asset('storage/fragancias/generic/frasco_nicho.png') }}';">
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">{{ $item->fragancia->casa_perfumista }}</div>
                    <h5 class="mb-1">{{ $item->fragancia->nombre }}</h5>
                    <div class="text-muted small mb-2">
                        @if($item->tamano) {{ $item->tamano->tamano }} @endif
                    </div>
                    @php
                        $precioUnitario = (float) $item->tamano->precioFinal();
                        $ivaItem = \App\Models\Configuracion::desglosarIva($precioUnitario);
                    @endphp
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="fw-600">${{ number_format($precioUnitario,2,',','.') }} c/u</span>
                        <form action="{{ route('carrito.actualizar', $item->id) }}" method="POST" class="d-flex align-items-center gap-1">
                            @csrf @method('PATCH')
                            <label class="text-muted small mb-0">×</label>
                            <input type="number" name="cantidad" value="{{ $item->cantidad }}"
                                   min="1" max="{{ $item->tamano->stock }}"
                                   class="form-control form-control-sm" style="width:70px"
                                   onchange="this.form.submit()">
                            <select name="regalo_presentacion" class="form-select form-select-sm ms-2"
                                    onchange="this.form.submit()" style="width:190px;">
                                <option value="blanco" {{ $item->regalo_presentacion === 'blanco' ? 'selected' : '' }}>
                                    Bolsa de regalo blanca (Gratis)
                                </option>
                                <option value="negro" {{ $item->regalo_presentacion === 'negro' ? 'selected' : '' }}>
                                    Bolsa de regalo negra (Gratis)
                                </option>
                            </select>
                        </form>
                        <span class="precio-item fw-700">
                            ${{ number_format($precioUnitario * $item->cantidad, 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="small text-muted mt-1">
                        <div>Sin IVA: ${{ number_format($ivaItem['base'] * $item->cantidad, 2, ',', '.') }}</div>
                        <div>IVA ({{ number_format($ivaItem['porcentaje'], 2, ',', '.') }}%): ${{ number_format($ivaItem['iva'] * $item->cantidad, 2, ',', '.') }}</div>
                        <div>Con IVA: ${{ number_format($ivaItem['conIva'] * $item->cantidad, 2, ',', '.') }}</div>
                    </div>
                    @if(!empty($item->regalo_presentacion))
                    @php($nombresRegalo = ['blanco' => 'Bolsa de regalo blanca', 'negro' => 'Bolsa de regalo negra'])
                    <div class="text-success small mt-2">
                        Envoltorio de regalo «{{ $nombresRegalo[$item->regalo_presentacion] ?? ucfirst($item->regalo_presentacion) }}» (incluido, sin costo)
                    </div>
                    @endif
                </div>
                <form id="fQuitarItem{{ $item->id }}" action="{{ route('carrito.quitar', $item->id) }}" method="POST"
                      onsubmit="return confirmarQuitarItem(event, {{ $item->id }}, '{{ addslashes($item->fragancia->nombre) }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
            @endforeach

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('catalogo') }}" class="btn btn-outline-secondary">← Seguir comprando</a>
                <form id="fVaciarCarrito" action="{{ route('carrito.vaciar') }}" method="POST"
                      onsubmit="return confirmarVaciarCarrito(event)">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Vaciar carrito</button>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="resumen-panel p-4 rounded-3">
                @php($umbralEnvioGratis = (float) \App\Models\Configuracion::obtener('envio_gratis_desde', config('comercial.envio_gratis_desde', 80)))
                @php($costoEnvio = (float) \App\Models\Configuracion::obtener('costo_envio_nacional', config('comercial.costo_envio_nacional', 5)))
                {{-- El umbral de envío gratis se evalúa sobre el subtotal de productos,
                     igual que CheckoutController::calcularEnvio(), sin contar el costo del
                     envoltorio de regalo. Antes esta vista comparaba $total (que sí incluye
                     el regalo) contra el umbral, mientras el checkout real comparaba solo el
                     subtotal: con costoRegalo en 0 no se notaba, pero en cuanto el envoltorio
                     tenga un precio, esta página podía mostrar "Envío gratis" mientras el
                     checkout seguía cobrando envío (o viceversa). --}}
                @php($envio = $subtotal >= $umbralEnvioGratis ? 0 : $costoEnvio)
                @php($totalConEnvio = $total + $envio)
                <h5 class="mb-3">Resumen del Pedido</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal sin IVA ({{ $items->sum('cantidad') }} items)</span>
                    <span>${{ number_format($iva['base'],2,',','.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-muted small">
                    <span>IVA ({{ number_format($iva['porcentaje'], 2, ',', '.') }}%)</span>
                    <span>${{ number_format($iva['iva'],2,',','.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-muted small">
                    <span>Precio con IVA</span>
                    <span>${{ number_format($subtotalConIva,2,',','.') }}</span>
                </div>
                @if($regaloTotal > 0)
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Envoltorio de regalo</span>
                    <span>${{ number_format($regaloTotal,2,',','.') }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Envío</span>
                    <span>{{ $envio == 0 ? 'Gratis' : '$' . number_format($envio,2,',','.') }}</span>
                </div>
                <p class="small text-muted mb-2">{{ $iva['incluido'] ? 'El precio de las fragancias ya incluye IVA.' : 'Las fragancias no incluyen IVA.' }}</p>
                <hr>
                <div class="d-flex justify-content-between fw-700 mb-4">
                    <span>Total</span>
                    <span>${{ number_format($totalConEnvio,2,',','.') }} USD</span>
                </div>
                <a href="{{ route('checkout.show') }}" class="btn btn-gold w-100 btn-lg" id="btnProcederPago">Proceder al pago</a>
                @if($subtotal < $umbralEnvioGratis)
                <p class="text-center mt-2 small text-muted">
                    Te faltan ${{ number_format($umbralEnvioGratis - $subtotal,2,',','.') }} para envío gratis
                </p>
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-5 empty-state">
        <div class="empty-state-illustration mb-3">
            <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="60" cy="60" r="58" fill="var(--ep-ivory)" stroke="var(--ep-border)" stroke-width="2"/>
                <rect x="47" y="50" width="26" height="38" rx="6" fill="#fff" stroke="var(--ep-gold)" stroke-width="2.5"/>
                <rect x="53" y="38" width="14" height="14" rx="2" fill="#fff" stroke="var(--ep-gold)" stroke-width="2.5"/>
                <rect x="56" y="30" width="8" height="10" rx="1.5" fill="var(--ep-gold)"/>
                <path d="M47 68 h26" stroke="var(--ep-border)" stroke-width="2"/>
                <circle cx="60" cy="94" r="3" fill="var(--ep-rose)" opacity=".55"/>
                <circle cx="72" cy="86" r="2" fill="var(--ep-gold)" opacity=".6"/>
                <circle cx="48" cy="86" r="2" fill="var(--ep-gold)" opacity=".6"/>
            </svg>
        </div>
        <h4>Tu carrito está vacío</h4>
        <p class="text-muted">Descubre nuestras fragancias de lujo y añade tus favoritas.</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="{{ route('catalogo') }}" class="btn btn-dark-perfume">Explorar fragancias</a>
            @auth
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Regresar al panel</a>
            @endauth
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnProcederPago');
    if (btn) {
        btn.addEventListener('click', function () {
            btn.classList.add('disabled');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
        });
    }
});

function confirmarQuitarItem(event, itemId, nombre) {
    event.preventDefault();
    confirmarAccion({
        titulo: 'Quitar del carrito',
        mensaje: '¿Quitar "' + nombre + '" del carrito?',
        textoBoton: 'Quitar',
        variante: 'danger',
        onConfirmar: function () {
            window.__submitFormSegura(document.getElementById('fQuitarItem' + itemId));
        }
    });
    return false;
}

function confirmarVaciarCarrito(event) {
    event.preventDefault();
    confirmarAccion({
        titulo: 'Vaciar carrito',
        mensaje: '¿Vaciar todo el carrito? Se quitarán todos los productos agregados.',
        textoBoton: 'Vaciar',
        variante: 'danger',
        onConfirmar: async function () {
            const form = document.getElementById('fVaciarCarrito');
            const csrf = form.querySelector('input[name="_token"]').value;
            const url = form.action;

            try {
                const respuesta = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                if (!respuesta.ok) {
                    throw new Error('No se pudo vaciar el carrito.');
                }

                window.location.reload();
            } catch (error) {
                console.error(error);
                if (form) {
                    window.__submitFormSegura(form);
                }
            }
        }
    });
    return false;
}
</script>
@endsection
