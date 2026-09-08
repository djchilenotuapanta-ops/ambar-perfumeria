@extends('layouts.template')
@section('title', $fragancia->nombre . ' — ' . $fragancia->casa_perfumista)
@section('meta_description', Str::limit(strip_tags($fragancia->descripcion), 155))
@section('og_type', 'product')
@section('og_image', $fragancia->imagenMostrar())
@section('canonical_url', route('fragancia.show', $fragancia->slug))

@section('contenido')
<div class="container py-5">
    <nav aria-label="ruta de navegación" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('index') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('catalogo') }}">Catálogo</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('familia.show', $fragancia->familia_id) }}">
                    {{ $fragancia->familia->nombre }}</a>
            </li>
            <li class="breadcrumb-item active">{{ $fragancia->nombre }}</li>
        </ol>
    </nav>

    @auth
        @if(auth()->user()->role === 'admin')
            <div class="mb-4">
                <a href="{{ route('admin.fragancias.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver al panel
                </a>
            </div>
        @elseif(auth()->user()->role === 'cliente')
            <div class="mb-4">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver al panel
                </a>
            </div>
        @endif
    @endauth

    <div class="row g-5">
        @php
            $tamanosPermitidos = [100, 50, 30];
            $tamanosDisponibles = $fragancia->tamanos
                ->filter(fn ($t) => in_array($t->ml(), $tamanosPermitidos, true))
                ->values();
            $primerTamano = $tamanosDisponibles->first() ?? $fragancia->tamanos->first();
        @endphp

        <div class="col-md-5">
            <div class="fragancia-detail-img-wrap">
                <img src="{{ $fragancia->imagenMostrar() }}"
                     alt="{{ $fragancia->nombre }}" class="fragancia-detail-img" id="imagenPrincipal"
                     onerror="this.onerror=null;this.src='{{ asset('storage/fragancias/generic/frasco_nicho.png') }}';">
            </div>
            @if($fragancia->imagenes->count())
            <div class="d-flex gap-2 mt-3 flex-wrap">
                @foreach($fragancia->imagenes as $img)
                <img src="{{ asset('storage/'.$img->ruta_imagen) }}" class="thumb-img" alt="{{ $fragancia->nombre }} - miniatura"
                     onerror="this.style.display='none';"
                     onclick="document.getElementById('imagenPrincipal').src=this.src">
                @endforeach
            </div>
            @endif
        </div>

        <div class="col-md-7">
            <div class="fragancia-detail-casa">{{ $fragancia->casa_perfumista }}</div>
            <h1 class="fragancia-detail-nombre">
                {{ $fragancia->nombre }}
            </h1>

            <div class="d-flex gap-2 flex-wrap mb-3">
                <span class="badge-perfume">{{ $fragancia->etiquetaGenero() }}</span>
                <span class="badge-perfume">
                    <i class="{{ $fragancia->familia->icono }} me-1"></i>
                    {{ $fragancia->familia->nombre }}
                </span>
            </div>

            @php
                $iva = \App\Models\Configuracion::desglosarIva((float) ($primerTamano?->precioFinal() ?? 0));
            @endphp
            <div class="fragancia-detail-precio mb-2">
                <span class="precio-grande" id="precioMostrado">
                    {{ $primerTamano ? '$' . number_format($primerTamano->precioFinal(), 2, ',', '.') : 'No disponible' }}
                </span>
                <span class="precio-tachado ms-2 {{ ($primerTamano && $primerTamano->tieneDescuento()) ? '' : 'd-none' }}" id="precioTachado">
                    ${{ $primerTamano ? number_format((float) $primerTamano->precio, 2, ',', '.') : '' }}
                </span>
                @if($primerTamano)
                <span class="precio-moneda ms-2">USD</span>
                @endif
            </div>
            @if($primerTamano)
            <div class="small text-muted mb-3" id="ivaContainer">
                <div>Sin IVA: $<span id="precioSinIva">{{ number_format($iva['base'], 2, ',', '.') }}</span></div>
                <div>IVA (<span id="ivaPorcentajeDisplay">{{ number_format($iva['porcentaje'], 2, ',', '.') }}</span>%): $<span id="ivaMonto">{{ number_format($iva['iva'], 2, ',', '.') }}</span></div>
                <div>Precio con IVA: $<span id="precioConIva">{{ number_format($iva['conIva'], 2, ',', '.') }}</span></div>
            </div>
            <p class="small text-muted mb-0">{{ $iva['incluido'] ? 'El precio mostrado ya incluye IVA.' : 'Las fragancias no incluyen IVA.' }}</p>
            <input type="hidden" id="ivaPorcentaje" value="{{ $iva['porcentaje'] }}">
            <input type="hidden" id="ivaIncluido" value="{{ $iva['incluido'] ? '1' : '0' }}">
            @endif

            <p class="fragancia-detail-desc">{{ $fragancia->descripcion }}</p>

            @if($tamanosDisponibles->count())
            <div class="mb-4">
                <span class="text-muted small d-block mb-2">Elige el tamaño:</span>
                <div class="d-flex gap-2 flex-wrap" id="selectorTamanos">
                    @foreach($tamanosDisponibles as $t)
                    <button type="button" class="btn-tamano {{ $loop->first ? 'active' : '' }} {{ $t->stock < 1 ? 'agotado' : '' }}"
                            data-id="{{ $t->id }}"
                            data-precio="{{ $t->precioFinal() }}"
                            data-precio-original="{{ round($t->precio) }}"
                            data-tiene-descuento="{{ $t->tieneDescuento() ? '1' : '0' }}"
                            data-stock="{{ $t->stock }}"
                            data-imagen="{{ $fragancia->imagenMostrarTamano($t->ml()) }}">
                        {{ $t->tamano }}
                        @if($t->stock < 1)<span class="d-block" style="font-size:.7rem;">Agotado</span>@endif
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            @auth
            <form action="{{ route('carrito.agregar') }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="fragancia_tamano_id" id="tamanoSeleccionado"
                       value="{{ $primerTamano->id ?? '' }}">

                @if($primerTamano)
                <div class="mb-3" id="avisoStock">
                    @if($primerTamano->stock < 1)
                        <span class="text-muted small">(agotado)</span>
                    @elseif($mostrarStockBajoCliente && $primerTamano->stock <= \App\Models\Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5)))
                        <span class="badge" style="background:#DC3545;">¡Solo quedan {{ $primerTamano->stock }}!</span>
                    @endif
                </div>
                @endif

                <div class="d-flex gap-3 align-items-center mb-3">
                    <div class="qty-control d-flex align-items-center">
                        <button type="button" class="qty-btn" onclick="qtyChange(-1)">−</button>
                        <input type="number" name="cantidad" id="qty" value="1" min="1"
                               max="{{ $primerTamano->stock ?? 1 }}" class="qty-input">
                        <button type="button" class="qty-btn" onclick="qtyChange(1)">+</button>
                    </div>
                    <button type="submit" class="btn btn-gold btn-lg flex-grow-1" id="btnAgregarCarrito"
                            {{ (!$primerTamano || $primerTamano->stock < 1) ? 'disabled' : '' }}>
                        <i class="fas fa-shopping-bag me-2"></i>
                        <span id="textoBotonCarrito">
                            {{ (!$primerTamano || $primerTamano->stock < 1) ? 'Sin stock' : 'Añadir al carrito' }}
                        </span>
                    </button>
                </div>
            </form>

            <div class="gift-cta mb-4 p-3 rounded-3">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div>
                        <h6 class="mb-1" style="color:#2C1810;">¿Es para regalo?</h6>
                        <p class="mb-2 small text-muted">Personaliza presentación, tarjeta y estilo de envoltura en un solo paso.</p>
                        <div class="d-flex flex-wrap gap-2 small">
                            <span class="badge bg-light text-dark border">🎁 Bolsa de regalo gratis</span>
                            <span class="badge bg-light text-dark border">✍️ Tarjeta dedicatoria</span>
                        </div>
                    </div>
                    <a href="{{ route('regalo') }}" class="btn btn-outline-dark btn-sm">
                        Configurar regalo
                    </a>
                </div>
            </div>
            @else
            <div class="alert alert-perfume mb-4">
                <a href="{{ route('login') }}">Inicia sesión</a> para añadir al carrito.
            </div>
            @endauth

            @if($fragancia->notas_salida || $fragancia->notas_corazon || $fragancia->notas_fondo)
            <div class="piramide-olfativa mt-4">
                <h4 class="piramide-title">Pirámide Olfativa</h4>
                <div class="row g-3">
                    @if($fragancia->notas_salida)
                    <div class="col-md-4">
                        <div class="nota-card nota-salida">
                            <div class="nota-icon">🌿</div>
                            <div class="nota-label">Notas de Salida</div>
                            <div class="nota-ingredientes">{{ $fragancia->notas_salida }}</div>
                            <div class="nota-tiempo">0 – 30 min</div>
                        </div>
                    </div>
                    @endif
                    @if($fragancia->notas_corazon)
                    <div class="col-md-4">
                        <div class="nota-card nota-corazon">
                            <div class="nota-icon">🌸</div>
                            <div class="nota-label">Notas de Corazón</div>
                            <div class="nota-ingredientes">{{ $fragancia->notas_corazon }}</div>
                            <div class="nota-tiempo">30 min – 4 h</div>
                        </div>
                    </div>
                    @endif
                    @if($fragancia->notas_fondo)
                    <div class="col-md-4">
                        <div class="nota-card nota-fondo">
                            <div class="nota-icon">🌳</div>
                            <div class="nota-label">Notas de Fondo</div>
                            <div class="nota-ingredientes">{{ $fragancia->notas_fondo }}</div>
                            <div class="nota-tiempo">4 h+</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-5 pt-4 border-top">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h3 class="mb-0">Reseñas de clientes</h3>
            @if($resenas->isNotEmpty())
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <span class="fw-700 text-dark">{{ $promedioResenas }}</span>
                    <span class="text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="{{ $i <= round($promedioResenas) ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                    </span>
                    <span>({{ $resenas->count() }} reseñas)</span>
                </div>
            @endif
        </div>

        @auth
            @if($miResena)
                <div class="alert alert-light border mb-4">
                    Ya dejaste una reseña para esta fragancia. Puedes verla abajo y actualizarla si quieres desde el panel de tu cuenta.
                </div>
            @else
                <form action="{{ route('reseñas.store', $fragancia->slug) }}" method="POST" class="border rounded-4 p-4 mb-4 bg-light-subtle">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-600">Tu valoración</label>
                            <select name="calificacion" class="form-select" required>
                                <option value="">Selecciona</option>
                                <option value="5">5 estrellas</option>
                                <option value="4">4 estrellas</option>
                                <option value="3">3 estrellas</option>
                                <option value="2">2 estrellas</option>
                                <option value="1">1 estrella</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-600">Comentario</label>
                            <textarea name="comentario" rows="3" class="form-control" placeholder="Cuéntanos qué te pareció la fragancia..." required></textarea>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-gold w-100">Enviar</button>
                        </div>
                    </div>
                </form>
            @endif
        @else
            <div class="alert alert-light border mb-4">
                <a href="{{ route('login') }}">Inicia sesión</a> para dejar tu reseña sobre esta fragancia.
            </div>
        @endauth

        @if($resenas->isEmpty())
            <div class="text-muted">Todavía no hay reseñas para esta fragancia. ¡Sé el primero en comentar!</div>
        @else
            <div class="row g-3">
                @foreach($resenas as $resena)
                    <div class="col-12 col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $resena->user->name ?? 'Usuario' }}</strong>
                                    <div class="small text-muted">{{ $resena->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $resena->calificacion ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="mb-0 text-muted">{{ $resena->comentario }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($relacionadas->count())
    <div class="mt-5 pt-4 border-top">
        <h3 class="mb-4">También te puede gustar</h3>
        <div class="row g-4">
            @foreach($relacionadas as $rel)
            <div class="col-6 col-md-3">
                @include('partials.fragancia-card', ['fragancia' => $rel])
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@endsection
@section('scripts')
<script>
function qtyChange(d) {
    const i = document.getElementById('qty');
    i.value = Math.max(1, Math.min(parseInt(i.max), parseInt(i.value) + d));
}

(function () {
    const umbralStockBajo = {{ (int) \App\Models\Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5)) }};
    const mostrarStockBajoCliente = @json((bool) $mostrarStockBajoCliente);

    document.querySelectorAll('#selectorTamanos .btn-tamano').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#selectorTamanos .btn-tamano').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const precio         = parseFloat(this.dataset.precio);
            const precioOriginal = parseFloat(this.dataset.precioOriginal);
            const tieneDescuento = this.dataset.tieneDescuento === '1';
            const stock          = parseInt(this.dataset.stock);
            const id              = this.dataset.id;
            const imagen          = this.dataset.imagen;

            const imgPrincipal = document.getElementById('imagenPrincipal');
            if (imgPrincipal && imagen) imgPrincipal.src = imagen;

            document.getElementById('precioMostrado').textContent =
                '$' + precio.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Actualizar IVA (respeta si el precio guardado ya incluye IVA,
            // igual que Configuracion::desglosarIva en el backend)
            const ivaPorcentaje = parseFloat(document.getElementById('ivaPorcentaje')?.value || 15);
            const ivaIncluido = document.getElementById('ivaIncluido')?.value === '1';

            let base, ivaMonto, precioConIva;
            if (ivaIncluido) {
                precioConIva = precio;
                base = ivaPorcentaje > 0 ? precio / (1 + ivaPorcentaje / 100) : precio;
                ivaMonto = precioConIva - base;
            } else {
                base = precio;
                ivaMonto = precio * (ivaPorcentaje / 100);
                precioConIva = base + ivaMonto;
            }

            const ivaContainer = document.getElementById('ivaContainer');
            if (ivaContainer) {
                document.getElementById('precioSinIva').textContent = base.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('ivaMonto').textContent = ivaMonto.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('precioConIva').textContent = precioConIva.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            const tachado = document.getElementById('precioTachado');
            if (tachado) {
                if (tieneDescuento) {
                    tachado.textContent = '$' + precioOriginal.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    tachado.classList.remove('d-none');
                } else {
                    tachado.classList.add('d-none');
                }
            }

            const inputOculto = document.getElementById('tamanoSeleccionado');
            if (inputOculto) inputOculto.value = id;

            const qty = document.getElementById('qty');
            if (qty) {
                qty.max = Math.max(stock, 1);
                qty.value = 1;
            }

            const aviso = document.getElementById('avisoStock');
            if (aviso) {
                if (stock < 1) {
                    aviso.innerHTML = '<span class="text-muted small">(agotado)</span>';
                } else if (mostrarStockBajoCliente && stock <= umbralStockBajo) {
                    aviso.innerHTML = '<span class="badge" style="background:#DC3545;">¡Solo quedan ' + stock + '!</span>';
                } else {
                    aviso.innerHTML = '';
                }
            }

            const btn   = document.getElementById('btnAgregarCarrito');
            const texto = document.getElementById('textoBotonCarrito');
            if (btn) {
                btn.disabled = stock < 1;
                if (texto) texto.textContent = stock < 1 ? 'Sin stock' : 'Añadir al carrito';
            }
        });
    });
})();
</script>
@endsection
