@extends('layouts.template')
@section('title', 'Inicio')
@section('meta_description', 'Descubre nuestra colección de fragancias originales de las mejores casas perfumistas. Envío a nivel nacional en Ecuador. Encuentra tu aroma ideal en Ambar Parfums.')
@section('canonical_url', route('index'))

@section('styles')
<style>
/* Envases strip */
.envases-strip { background: linear-gradient(180deg, var(--ep-ivory) 0%, #fff 100%); }
.envases-strip .envases-row {
    padding: 10px 4px 18px; scroll-snap-type: x proximity;
    justify-content: center; align-items: center;
}
.envases-strip-header { border-bottom: 1px solid var(--ep-border); padding-bottom: 1.1rem; margin-bottom: 1.6rem; }
.envases-strip-header .section-title { font-size: 1.6rem; margin-bottom: .15rem; }
.envases-strip-note { font-size: .82rem; color: var(--ep-muted); }
.envases-strip-note i { color: var(--ep-gold); margin-right: .35rem; }

.envase-item { min-width: 240px; flex: 0 0 auto; transition: transform .22s ease; scroll-snap-align: start; }
.envase-item:hover { transform: translateY(-8px); }
.envase-link { display: block; color: inherit; text-decoration: none; }

.envase-card {
    background: #fff; border: 1px solid var(--ep-border); border-radius: 20px;
    padding: 1.4rem 1.2rem 1.5rem; text-align: center;
    box-shadow: 0 4px 14px rgba(44,24,16,.05);
    transition: box-shadow .22s ease, border-color .22s ease;
}
.envase-link:hover .envase-card {
    border-color: var(--ep-gold);
    box-shadow: 0 16px 34px rgba(44,24,16,.14);
}

.envase-bubble {
    width: 165px; height: 165px; border-radius: 50%; position: relative;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 1.1rem;
    background: linear-gradient(180deg,#fff,#f7f2ee);
    border: 1px solid var(--ep-border); overflow: hidden;
}
.envase-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.envase-bubble .fa-flask { font-size: 1.6rem; color: var(--ep-gold); }

.envase-size-badge {
    position: absolute; bottom: -2px; left: 50%; transform: translateX(-50%);
    background: var(--ep-primary); color: var(--ep-gold);
    font-size: .68rem; font-weight: 700; letter-spacing: .5px;
    padding: .22rem .65rem; border-radius: 99px; white-space: nowrap;
    box-shadow: 0 3px 8px rgba(44,24,16,.25);
}

.envase-cat {
    color: var(--ep-primary); font-size: 1rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center; gap: .4rem;
}
.envase-cat i { color: var(--ep-gold); font-size: .85rem; }
.envase-count { font-size: .78rem; color: var(--ep-muted); margin-top: .2rem; }

.envases-row::-webkit-scrollbar { height: 6px; }
.envases-row::-webkit-scrollbar-thumb { background: var(--ep-border); border-radius: 10px; }
.envases-row::-webkit-scrollbar-thumb:hover { background: var(--ep-gold); }

@media (max-width: 575.98px) {
    .envase-item { min-width: 180px; }
    .envase-bubble { width: 130px; height: 130px; }
}

/* ===== Testimonios ===== */
.testimonios-section {
    position: relative;
    background: linear-gradient(180deg, #fff 0%, var(--ep-ivory) 100%);
    overflow: hidden;
}
.testimonios-section::before {
    content: "";
    position: absolute; inset: 0;
    background-image: radial-gradient(var(--ep-border) 1px, transparent 1px);
    background-size: 22px 22px;
    opacity: .35;
    pointer-events: none;
}
.testimonios-quote-icon {
    font-size: 1.6rem; color: var(--ep-gold); opacity: .8;
}
.testimonios-viewport {
    position: relative;
    max-width: 780px;
    margin: 0 auto;
}
.testimonios-track {
    position: relative;
    min-height: 300px;
}
.testimonio-slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    transform: translateY(14px) scale(.985);
    transition: opacity .5s ease, transform .5s ease;
    pointer-events: none;
}
.testimonio-slide.is-active {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
    position: relative;
}
.testimonio-card {
    background: #fff;
    border: 1px solid var(--ep-border);
    border-radius: 22px;
    padding: 2.4rem 2.2rem;
    text-align: center;
    box-shadow: 0 18px 44px rgba(44,24,16,.08);
}
.testimonio-stars { color: var(--ep-gold); font-size: .95rem; letter-spacing: 3px; margin-bottom: 1rem; }
.testimonio-stars i.far { color: var(--ep-border); }
.testimonio-texto {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 1.35rem;
    line-height: 1.55;
    color: var(--ep-text);
    margin-bottom: 1.6rem;
}
@media (max-width: 575.98px) {
    .testimonio-texto { font-size: 1.12rem; }
    .testimonio-card { padding: 1.9rem 1.3rem; }
}
.testimonio-autor { display: flex; align-items: center; justify-content: center; gap: .75rem; }
.testimonio-avatar {
    width: 46px; height: 46px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, var(--ep-gold), #C49A28);
    color: var(--ep-primary); font-weight: 700; font-size: .95rem;
    flex-shrink: 0;
}
.testimonio-autor-info { text-align: left; }
.testimonio-nombre { font-weight: 600; color: var(--ep-primary); font-size: .92rem; line-height: 1.2; }
.testimonio-meta { font-size: .76rem; color: var(--ep-muted); }
.testimonio-meta i { color: var(--ep-gold); margin-right: .25rem; }

.testimonios-nav-btn {
    width: 42px; height: 42px; border-radius: 50%;
    border: 1px solid var(--ep-border); background: #fff; color: var(--ep-primary);
    display: flex; align-items: center; justify-content: center;
    transition: .2s ease; flex-shrink: 0;
}
.testimonios-nav-btn:hover { background: var(--ep-primary); color: var(--ep-gold); border-color: var(--ep-primary); }
.testimonios-controls { display: flex; align-items: center; justify-content: center; gap: 1.4rem; margin-top: 1.8rem; }
.testimonios-dots { display: flex; gap: .45rem; }
.testimonios-dot {
    width: 8px; height: 8px; border-radius: 50%; background: var(--ep-border);
    border: none; padding: 0; cursor: pointer; transition: .25s ease;
}
.testimonios-dot.is-active { background: var(--ep-gold); width: 22px; border-radius: 5px; }

.testimonios-resumen {
    display: flex; align-items: center; justify-content: center; gap: .6rem;
    margin-top: 2.2rem; flex-wrap: wrap;
}
.testimonios-resumen .rating-num { font-family: 'Cormorant Garamond', serif; font-size: 1.9rem; color: var(--ep-primary); font-weight: 600; }
.testimonios-resumen .rating-stars { color: var(--ep-gold); font-size: .85rem; }
.testimonios-resumen .rating-count { font-size: .8rem; color: var(--ep-muted); }

/* ===== Banner de Ofertas ===== */
.ofertas-banner {
    position: relative;
    background: linear-gradient(120deg, #1a0a08 0%, #2C1810 55%, #4A2520 100%);
    overflow: hidden;
}
.ofertas-banner::before {
    content: "";
    position: absolute; inset: 0;
    background-image:
        radial-gradient(circle at 12% 20%, rgba(212,175,55,.16) 0%, transparent 40%),
        radial-gradient(circle at 88% 80%, rgba(212,175,55,.12) 0%, transparent 45%);
    pointer-events: none;
}
.ofertas-banner-stripe {
    position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: repeating-linear-gradient(45deg, var(--ep-gold) 0 14px, #C49A28 14px 28px);
}
.ofertas-banner-head {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 1rem; padding: 1.6rem 0 1.2rem;
    position: relative; z-index: 1;
}
.ofertas-banner-tag {
    display: inline-flex; align-items: center; gap: .4rem;
    background: rgba(212,175,55,.14); border: 1px solid rgba(212,175,55,.4);
    color: var(--ep-gold); font-size: .68rem; font-weight: 700;
    letter-spacing: 2.5px; text-transform: uppercase;
    padding: .3rem .8rem; border-radius: 99px; margin-bottom: .55rem;
}
.ofertas-banner-tag i { animation: ofertas-pulse 1.6s ease-in-out infinite; }
@keyframes ofertas-pulse { 0%,100% { opacity:1; } 50% { opacity:.35; } }
.ofertas-banner-titulo {
    font-family: 'Cormorant Garamond', serif; color: #fff;
    font-size: 2.1rem; line-height: 1.15; margin: 0;
}
.ofertas-banner-titulo span { color: var(--ep-gold); }
.ofertas-banner-sub { color: #C4A882; font-size: .88rem; margin-top: .3rem; }
.ofertas-banner-cta {
    white-space: nowrap;
}
@media (max-width: 767.98px) {
    .ofertas-banner-titulo { font-size: 1.55rem; }
}

.ofertas-track {
    display: flex; gap: 1rem; overflow-x: auto; padding: .2rem .2rem 1.8rem;
    scroll-snap-type: x proximity; position: relative; z-index: 1;
}
.ofertas-track::-webkit-scrollbar { height: 6px; }
.ofertas-track::-webkit-scrollbar-thumb { background: rgba(212,175,55,.4); border-radius: 10px; }

.oferta-item { min-width: 208px; max-width: 208px; flex: 0 0 auto; scroll-snap-align: start; }
.oferta-card {
    background: #fff; border-radius: 16px; overflow: hidden;
    box-shadow: 0 14px 34px rgba(0,0,0,.28);
    transition: transform .22s ease, box-shadow .22s ease;
    display: flex; flex-direction: column; height: 100%;
}
.oferta-item:hover .oferta-card { transform: translateY(-6px); box-shadow: 0 20px 42px rgba(0,0,0,.35); }
.oferta-card-img-wrap {
    position: relative; background: linear-gradient(180deg,#fff,#f7f2ee);
    display: flex; align-items: center; justify-content: center; height: 150px;
}
.oferta-card-img { width: 100%; height: 100%; object-fit: contain; padding: .8rem; }
.oferta-descuento-badge {
    position: absolute; top: 8px; left: 8px;
    background: var(--ep-rose); color: #fff; font-size: .72rem; font-weight: 700;
    padding: .25rem .55rem; border-radius: 99px; box-shadow: 0 3px 8px rgba(0,0,0,.25);
}
.oferta-card-body { padding: .85rem .9rem 1rem; flex-grow: 1; display: flex; flex-direction: column; }
.oferta-card-casa { font-size: .68rem; color: var(--ep-muted); text-transform: uppercase; letter-spacing: 1px; }
.oferta-card-nombre {
    font-size: .88rem; font-weight: 600; color: var(--ep-primary);
    line-height: 1.25; margin: .15rem 0 .5rem;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.oferta-card-nombre a { color: inherit; text-decoration: none; }
.oferta-card-precios { margin-top: auto; display: flex; align-items: baseline; gap: .45rem; }
.oferta-precio-final { color: var(--ep-primary); font-weight: 700; font-size: 1.02rem; }
.oferta-precio-antes { color: var(--ep-muted); font-size: .78rem; text-decoration: line-through; }

.ofertas-banner-nav { display: flex; gap: .5rem; }
.ofertas-nav-btn {
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(255,255,255,.08); border: 1px solid rgba(212,175,55,.35);
    color: var(--ep-gold); display: flex; align-items: center; justify-content: center;
    transition: .2s ease;
}
.ofertas-nav-btn:hover { background: var(--ep-gold); color: var(--ep-primary); }

/* ===== Familias Olfativas ===== */
.familias-container { width: 100%; }

.familias-grid {
    display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;
    transition: all .3s ease;
}

.familia-item {
    flex: 0 0 auto;
    width: 220px;
}

.familia-card {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 2rem 1.4rem; background: var(--ep-ivory); border: 1.5px solid var(--ep-border);
    border-radius: 18px; transition: all .25s; cursor: pointer; height: 100%;
    box-shadow: 0 4px 14px rgba(44,24,16,.05);
}

.familia-card:hover {
    border-color: var(--ep-gold); background: var(--ep-cream); transform: translateY(-6px);
    box-shadow: 0 12px 28px rgba(44,24,16,.12);
}

.familia-icon { font-size: 2.2rem; color: var(--ep-gold-a11y); margin-bottom: .8rem; }
.familia-nombre { font-size: .95rem; font-weight: 600; color: var(--ep-primary); text-align: center; }
.familia-count { font-size: .75rem; color: var(--ep-muted); margin-top: .25rem; }

.familias-expand-trigger {
    text-align: center; padding: 1.5rem; cursor: pointer;
    color: var(--ep-gold); font-weight: 600; font-size: .9rem;
    transition: all .25s ease;
    display: flex; align-items: center; justify-content: center; gap: .6rem;
}

.familias-expand-trigger:hover { color: var(--ep-primary); }

.familias-expand-trigger i {
    transition: transform .3s ease;
}

@media (max-width: 768px) {
    .familia-item { width: 160px; }
    .familia-card { padding: 1.4rem 1rem; }
    .familia-icon { font-size: 1.8rem; }
}
</style>
@endsection

@section('contenido')

<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content text-center">
        <p class="hero-eyebrow">✦ FRAGANCIAS DE LUJO ✦</p>
        <h1 class="hero-title">Descubre Tu<br>Aroma Definitivo</h1>
        <p class="hero-subtitle">Colecciones exclusivas de las mejores casas perfumistas del mundo</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('catalogo') }}" class="btn btn-gold btn-lg">Ver Catálogo</a>
        </div>
    </div>
</section>

@if($ofertas->count())
<section class="ofertas-banner">
    <div class="ofertas-banner-stripe"></div>
    <div class="container">
        <div class="ofertas-banner-head">
            <div>
                <span class="ofertas-banner-tag"><i class="fas fa-bolt"></i> Tiempo limitado</span>
                <h2 class="ofertas-banner-titulo">Ofertas <span>Especiales</span> de Temporada</h2>
                <p class="ofertas-banner-sub mb-0">Hasta {{ $ofertas->max(fn($f) => $f->descuentoPorcentaje()) }}% de descuento en fragancias seleccionadas · Stock limitado</p>
            </div>
            <div class="d-flex align-items-center gap-3 ofertas-banner-cta">
                <a href="{{ route('catalogo', ['oferta' => 1]) }}" class="btn btn-gold btn-sm">Ver Todas las Ofertas</a>
                <div class="ofertas-banner-nav d-none d-md-flex" data-ofertas-nav>
                    <button type="button" class="ofertas-nav-btn" data-ofertas-prev aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>
                    <button type="button" class="ofertas-nav-btn" data-ofertas-next aria-label="Siguiente"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>

        <div class="ofertas-track" data-ofertas-track>
            @foreach($ofertas as $f)
            <div class="oferta-item">
                <a href="{{ route('fragancia.show', $f->slug) }}" class="oferta-card text-decoration-none">
                    <div class="oferta-card-img-wrap">
                        <span class="oferta-descuento-badge">-{{ $f->descuentoPorcentaje() }}%</span>
                        <img src="{{ $f->imagenMostrar() }}" alt="{{ $f->nombre }}" class="oferta-card-img" loading="lazy"
                             onerror="this.onerror=null;this.src='{{ asset('storage/fragancias/generic/frasco_nicho.png') }}';">
                    </div>
                    <div class="oferta-card-body">
                        <div class="oferta-card-casa">{{ $f->casa_perfumista }}</div>
                        <div class="oferta-card-nombre">{{ $f->nombre }}</div>
                        <div class="oferta-card-precios">
                            <span class="oferta-precio-final">${{ number_format($f->precioFinal(), 0, ',', '.') }}</span>
                            <span class="oferta-precio-antes">${{ number_format($f->precioAntesDescuento(), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-ofertas-track]').forEach(function (track) {
        var prev = track.parentElement.querySelector('[data-ofertas-prev]');
        var next = track.parentElement.querySelector('[data-ofertas-next]');
        var scrollAmount = 232;
        if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -scrollAmount * 2, behavior: 'smooth' }); });
        if (next) next.addEventListener('click', function () { track.scrollBy({ left: scrollAmount * 2, behavior: 'smooth' }); });
    });
});
</script>
@endif

@include('partials.envases-strip', ['envases' => $envases])

<section class="section-familias py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <p class="eyebrow">Explora por</p>
            <h2 class="section-title">Familias Olfativas</h2>
        </div>
        <div class="familias-container">
            <div class="familias-grid" id="familiasGrid">
                @php
                    $familiasVisibles = $familias->take(3);
                @endphp
                @foreach($familiasVisibles as $f)
                <div class="familia-item">
                    <a href="{{ route('familia.show', $f->id) }}" class="familia-card text-decoration-none">
                        <div class="familia-icon"><i class="{{ $f->icono }}"></i></div>
                        <div class="familia-nombre">{{ $f->nombre }}</div>
                        <div class="familia-count">{{ $f->fragancias_count ?? $f->fragancias()->count() }} frag.</div>
                    </a>
                </div>
                @endforeach
            </div>
            @if($familias->count() > 3)
            <div style="text-align: center; margin-top: 2rem;">
                <a href="{{ route('familias.index') }}" class="familias-expand-trigger" style="display: inline-flex; text-decoration: none; cursor: pointer;">
                    <span class="expand-text">Ver todas las categorías</span>
                    <i class="fas fa-arrow-right" style="margin-left: .6rem;"></i>
                </a>
            </div>
            @endif
        </div>
    </div>
</section>

@if($masVendidas->count())
<section class="section-mas-vendidas py-5 bg-ivory">
    <div class="container">
        <div class="section-header text-center mb-5">
            <p class="eyebrow">Elegidas por nuestros clientes</p>
            <h2 class="section-title">{{ \App\Models\Configuracion::obtener('top_ventas_titulo', config('comercial.top_ventas.titulo', 'Las Más Vendidas')) }}</h2>
        </div>
        <div class="row g-4">
            @foreach($masVendidas as $i => $frag)
            <div class="col-6 col-md-4 col-lg-3">
                @include('partials.fragancia-card', ['fragancia' => $frag, 'badge' => '#' . ($i + 1) . ' Más vendida'])
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="testimonios-section py-5">
    <div class="container position-relative">
        <div class="section-header text-center mb-4">
            <p class="eyebrow">Lo que dicen de nosotros</p>
            <h2 class="section-title">Reseñas de Clientes</h2>
        </div>

        @php
        $testimonios = [
            ['nombre' => 'Valentina Reyes', 'ubicacion' => 'Quito, Ecuador', 'rating' => 5,
             'texto' => 'La calidad de las fragancias es impresionante y la atención al cliente superó mis expectativas. Mi pedido llegó perfectamente embalado y a tiempo.'],
            ['nombre' => 'Andrés Molina', 'ubicacion' => 'Guayaquil, Ecuador', 'rating' => 5,
             'texto' => 'Compré un perfume de regalo y vino con una muestra de cortesía. El aroma es idéntico al original y el envío fue más rápido de lo esperado.'],
            ['nombre' => 'Camila Ortiz', 'ubicacion' => 'Cuenca, Ecuador', 'rating' => 4,
             'texto' => 'Excelente variedad de familias olfativas. Me ayudaron a elegir la fragancia ideal para mi tipo de piel y clima. Definitivamente volveré a comprar.'],
            ['nombre' => 'Daniel Torres', 'ubicacion' => 'Manta, Ecuador', 'rating' => 5,
             'texto' => 'Llevo tres pedidos con ellos y siempre es la misma experiencia impecable: productos originales, buen precio y soporte muy atento por chat.'],
        ];
        $promedio = round(collect($testimonios)->avg('rating'), 1);
        @endphp

        <div class="testimonios-viewport" data-testimonios>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="testimonios-nav-btn d-none d-md-flex" data-testimonios-prev aria-label="Reseña anterior">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="testimonios-track flex-grow-1" data-testimonios-track>
                    @foreach($testimonios as $i => $t)
                    <div class="testimonio-slide {{ $i === 0 ? 'is-active' : '' }}" data-testimonio-slide>
                        <div class="testimonio-card">
                            <div class="testimonios-quote-icon"><i class="fas fa-quote-left"></i></div>
                            <div class="testimonio-stars" aria-label="{{ $t['rating'] }} de 5 estrellas">
                                @for($s = 1; $s <= 5; $s++)
                                    <i class="{{ $s <= $t['rating'] ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </div>
                            <p class="testimonio-texto">"{{ $t['texto'] }}"</p>
                            <div class="testimonio-autor">
                                <div class="testimonio-avatar">{{ collect(explode(' ', $t['nombre']))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}</div>
                                <div class="testimonio-autor-info">
                                    <div class="testimonio-nombre">{{ $t['nombre'] }}</div>
                                    <div class="testimonio-meta"><i class="fas fa-map-marker-alt"></i>{{ $t['ubicacion'] }} · Compra verificada</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="testimonios-nav-btn d-none d-md-flex" data-testimonios-next aria-label="Siguiente reseña">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="testimonios-controls">
                <button type="button" class="testimonios-nav-btn d-flex d-md-none" data-testimonios-prev aria-label="Reseña anterior">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="testimonios-dots" data-testimonios-dots>
                    @foreach($testimonios as $i => $t)
                        <button type="button" class="testimonios-dot {{ $i === 0 ? 'is-active' : '' }}" data-testimonios-dot="{{ $i }}" aria-label="Ir a reseña {{ $i + 1 }}"></button>
                    @endforeach
                </div>
                <button type="button" class="testimonios-nav-btn d-flex d-md-none" data-testimonios-next aria-label="Siguiente reseña">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="testimonios-resumen">
            <span class="rating-num">{{ $promedio }}</span>
            <span class="rating-stars">
                @for($s = 1; $s <= 5; $s++)
                    <i class="{{ $s <= round($promedio) ? 'fas' : 'far' }} fa-star"></i>
                @endfor
            </span>
            <span class="rating-count">basado en {{ count($testimonios) }}+ reseñas de clientes</span>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-testimonios]').forEach(function (root) {
        var slides = root.querySelectorAll('[data-testimonio-slide]');
        var dots = root.querySelectorAll('[data-testimonios-dot]');
        var prevBtns = root.querySelectorAll('[data-testimonios-prev]');
        var nextBtns = root.querySelectorAll('[data-testimonios-next]');
        var current = 0;
        var timer = null;

        function show(index) {
            current = (index + slides.length) % slides.length;
            slides.forEach(function (slide, i) { slide.classList.toggle('is-active', i === current); });
            dots.forEach(function (dot, i) { dot.classList.toggle('is-active', i === current); });
        }

        function next() { show(current + 1); }
        function prev() { show(current - 1); }

        function startAutoplay() {
            stopAutoplay();
            timer = setInterval(next, 6000);
        }
        function stopAutoplay() { if (timer) clearInterval(timer); }

        prevBtns.forEach(function (btn) { btn.addEventListener('click', function () { prev(); startAutoplay(); }); });
        nextBtns.forEach(function (btn) { btn.addEventListener('click', function () { next(); startAutoplay(); }); });
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                show(parseInt(dot.getAttribute('data-testimonios-dot'), 10));
                startAutoplay();
            });
        });

        root.addEventListener('mouseenter', stopAutoplay);
        root.addEventListener('mouseleave', startAutoplay);

        if (slides.length > 1) startAutoplay();
    });
});
</script>

<section class="beneficios-strip py-4" style="background:linear-gradient(135deg,#2C1810,#4A2520);">
    <div class="container">
        <div class="row text-center">
            @foreach([
                ['fas fa-certificate','100% Auténtico','Directo de las casas perfumistas'],
                ['fas fa-shipping-fast','Envío Rápido','Envío gratis desde $' . number_format((float) \App\Models\Configuracion::obtener('envio_gratis_desde', config('comercial.envio_gratis_desde', 80)), 0, '.', ',') . ' USD'],
                ['fas fa-vial','Muestras Gratis','Con cada pedido de cliente registrado'],
                ['fas fa-headset','Asesoría Experta','Consultores disponibles 24/7'],
            ] as [$icon,$titulo,$desc])
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="beneficio-item text-white">
                    <i class="{{ $icon }} fa-2x mb-2" style="color:#D4AF37;"></i>
                    <div class="fw-600" style="font-size:.9rem;">{{ $titulo }}</div>
                    <div style="font-size:.78rem;color:#C4A882;">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
