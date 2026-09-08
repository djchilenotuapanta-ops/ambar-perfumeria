@extends('layouts.template')
@section('title', isset($familia) ? $familia->nombre : 'Catálogo')
@section('meta_description', isset($familia)
    ? 'Descubre nuestra selección de fragancias ' . $familia->nombre . '. Perfumes originales con envío a nivel nacional en Ecuador.'
    : 'Explora todo nuestro catálogo de fragancias originales: hombre, mujer y unisex, de las mejores casas perfumistas.')

@section('styles')
<style>
.envases-presentaciones { position: relative; }
.envases-header .section-title { margin-bottom:.35rem; }

.envases-size-switch {
    display:inline-flex; gap:.25rem; padding:.3rem;
    background: var(--ep-cream); border-radius:50px;
    margin: 0 auto 2rem; justify-content:center;
    width:100%; max-width:360px;
}
.envase-size-btn {
    flex:1; border:none; background:transparent; cursor:pointer;
    padding:.55rem 1rem; border-radius:50px; font-weight:600;
    font-size:.95rem; color:var(--ep-muted); transition:all .25s ease;
}
.envase-size-btn span { font-size:.7rem; font-weight:500; opacity:.8; }
.envase-size-btn.active {
    background: linear-gradient(135deg, var(--ep-gold), #C49A28);
    color: var(--ep-primary);
    box-shadow: 0 4px 14px rgba(212,175,55,.35);
}
.envase-size-btn:not(.active):hover { color:var(--ep-primary); }

.envase-showcase-card {
    display:block; text-decoration:none; border-radius:18px; overflow:hidden;
    position:relative; box-shadow: 0 8px 24px rgba(44,24,16,.08);
    border:1px solid var(--ep-border); transition: transform .35s ease, box-shadow .35s ease;
}
.envase-showcase-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 18px 40px rgba(44,24,16,.18);
}
.envase-showcase-media {
    position:relative; aspect-ratio: 3 / 4; overflow:hidden; background:var(--ep-cream);
}
.envase-showcase-media img {
    width:100%; height:100%; object-fit:contain; object-position:center center;
    display:block; padding:1rem; background:var(--ep-ivory);
    transition: transform .6s ease;
}
.envase-showcase-card:hover .envase-showcase-media img { transform: scale(1.08); }

.envase-showcase-empty {
    width:100%; height:100%; display:flex; align-items:center; justify-content:center;
    color: var(--ep-muted); font-size:2rem;
}
.envase-showcase-size {
    position:absolute; top:12px; right:12px; z-index:2;
    background: rgba(26,10,8,.55); backdrop-filter: blur(3px);
    color:#fff; font-size:.72rem; font-weight:600; letter-spacing:.5px;
    padding:.3rem .65rem; border-radius:50px;
}
.envase-showcase-overlay {
    position:absolute; left:0; right:0; bottom:0; z-index:2;
    padding:1.5rem 1.1rem 1.1rem;
    background: linear-gradient(180deg, transparent, rgba(26,10,8,.15) 30%, rgba(26,10,8,.92) 100%);
    display:flex; flex-direction:column; gap:.15rem;
}
.envase-showcase-icon {
    color: var(--ep-gold); font-size:.85rem; margin-bottom:.15rem;
}
.envase-showcase-label {
    font-family:'Cormorant Garamond', serif; font-size:1.6rem; color:#fff;
    line-height:1.1; letter-spacing:.5px;
}
.envase-showcase-cta {
    font-size:.78rem; color: var(--ep-gold); font-weight:600;
    opacity:0; transform: translateY(6px); transition: all .3s ease;
    display:flex; align-items:center; gap:.35rem;
}
.envase-showcase-cta i { font-size:.7rem; transition: transform .3s ease; }
.envase-showcase-card:hover .envase-showcase-cta { opacity:1; transform: translateY(0); }
.envase-showcase-card:hover .envase-showcase-cta i { transform: translateX(4px); }

@media (max-width: 767px) {
    .envase-showcase-label { font-size:1.35rem; }
    .envases-size-switch { max-width:100%; }
}
@media (hover: none) {
    .envase-showcase-cta { opacity:1; transform:none; }
}
</style>
@endsection

@section('contenido')
<div class="container py-5">
    <div class="row">

        <div class="col-lg-3 mb-4">
            <div class="filtros-panel">
                <h5 class="filtros-title">Filtros</h5>
                <form action="{{ route('catalogo') }}" method="GET" id="formFiltros">

                    <div class="filtro-grupo position-relative">
                        <label class="filtro-label">Búsqueda</label>
                        <input type="text" name="buscar" id="inputBuscar" class="form-control" autocomplete="off"
                               placeholder="Nombre o casa..." value="{{ request('buscar') }}">
                        <div id="sugerenciasBusqueda" class="list-group position-absolute w-100 shadow-sm"
                             style="z-index:1000;display:none;max-height:320px;overflow-y:auto;"></div>
                        <div id="spinnerBusqueda" class="spinner-border spinner-border-sm text-secondary position-absolute"
                             style="right:10px;top:38px;display:none;" role="status"></div>
                    </div>

                    <div class="filtro-grupo">
                        <label class="filtro-label">Familia Olfativa</label>
                        @foreach($familias as $fam)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="familia"
                                   id="fam{{ $fam->id }}" value="{{ $fam->id }}"
                                   {{ request('familia') == $fam->id ? 'checked' : '' }}>
                            <label class="form-check-label" for="fam{{ $fam->id }}">
                                <i class="{{ $fam->icono }} me-1"></i>{{ $fam->nombre }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <div class="filtro-grupo">
                        <label class="filtro-label">Género</label>
                        @foreach([['mujer','🌸 Mujer'],['hombre','🌲 Hombre'],['unisex','✦ Unisex']] as [$val,$lbl])
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="genero"
                                   id="gen{{ $val }}" value="{{ $val }}"
                                   {{ request('genero') === $val ? 'checked' : '' }}>
                            <label class="form-check-label" for="gen{{ $val }}">{{ $lbl }}</label>
                        </div>
                        @endforeach
                    </div>

                    <div class="filtro-grupo">
                        <label class="filtro-label">Ordenar por</label>
                        <select name="orden" class="form-select">
                            <option value="">Relevancia</option>
                            <option value="precio_asc"  {{ request('orden')=='precio_asc'?'selected':'' }}>Precio: menor a mayor</option>
                            <option value="precio_desc" {{ request('orden')=='precio_desc'?'selected':'' }}>Precio: mayor a menor</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-dark-perfume" id="btnAplicarFiltros">Aplicar Filtros</button>
                        <a href="{{ route('catalogo') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            @if(isset($familia))
            <div class="catalogo-header mb-4">
                <h2>{{ $familia->nombre }}</h2>
                <p style="color:#8B6F5E;">{{ $familia->descripcion }}</p>
            </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3 flex-column flex-md-row gap-2">
                <span class="text-muted">{{ $fragancias->total() }} fragancias encontradas</span>
                @auth
                @php($panelRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('dashboard'))
                <a href="{{ $panelRoute }}" class="btn btn-outline-secondary btn-sm">← Volver al panel</a>
                @endauth
            </div>

            @include('app.front.partials.envases-presentaciones', ['envases' => $envases])

            @if($fragancias->count())
            <div class="row g-4">
                @foreach($fragancias as $frag)
                <div class="col-6 col-md-4">
                    @include('partials.fragancia-card', ['fragancia' => $frag])
                </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $fragancias->links() }}</div>
            @else
            <div class="text-center py-5 empty-state">
                <div class="empty-state-illustration mb-3">
                    <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="60" cy="60" r="58" fill="var(--ep-ivory)" stroke="var(--ep-border)" stroke-width="2"/>
                        <circle cx="53" cy="53" r="19" fill="#fff" stroke="var(--ep-gold)" stroke-width="3"/>
                        <path d="M67 67 L82 82" stroke="var(--ep-gold)" stroke-width="4" stroke-linecap="round"/>
                        <path d="M46 53 q7 -9 14 0" stroke="var(--ep-rose)" stroke-width="2" fill="none" opacity=".7"/>
                        <circle cx="53" cy="53" r="4" fill="var(--ep-rose)" opacity=".55"/>
                    </svg>
                </div>
                <h4>No se encontraron fragancias</h4>
                <p class="text-muted">Intenta con otros filtros.</p>
                <a href="{{ route('catalogo') }}" class="btn btn-dark-perfume">Ver todo el catálogo</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const input = document.getElementById('inputBuscar');
    const caja  = document.getElementById('sugerenciasBusqueda');
    const spinner = document.getElementById('spinnerBusqueda');
    let temporizador = null;

    function ocultar() {
        caja.style.display = 'none';
        caja.innerHTML = '';
        spinner.style.display = 'none';
    }

    function escaparHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function buscar(termino) {
        spinner.style.display = 'block';
        fetch(`{{ route('catalogo.buscar') }}?q=${encodeURIComponent(termino)}`)
            .then(r => r.json())
            .then(resultados => {
                spinner.style.display = 'none';
                if (!resultados.length) {
                    caja.innerHTML = `
                        <div class="list-group-item text-muted small text-center py-3">
                            Sin resultados para "${escaparHtml(termino)}"
                        </div>
                    `;
                    caja.style.display = 'block';
                    return;
                }

                caja.innerHTML = resultados.map(f => `
                    <a href="${escaparHtml(f.url)}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        ${f.imagen
                            ? `<img src="${escaparHtml(f.imagen)}" alt="${escaparHtml(f.nombre)}" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">`
                            : `<div style="width:36px;height:36px;border-radius:6px;background:#F5EFE8;"></div>`}
                        <div class="flex-grow-1">
                            <div class="small fw-600">${escaparHtml(f.nombre)}</div>
                            <div class="text-muted" style="font-size:.75rem;">${escaparHtml(f.casa)}</div>
                        </div>
                        <div class="small text-nowrap">$${escaparHtml(String(f.precio))}</div>
                    </a>
                `).join('');
                caja.style.display = 'block';
            })
            .catch(() => ocultar());
    }

    input.addEventListener('input', function () {
        clearTimeout(temporizador);
        const termino = this.value.trim();

        if (termino.length < 2) { ocultar(); return; }

        // Pequeño retraso para no disparar una petición por cada tecla.
        temporizador = setTimeout(() => buscar(termino), 300);
    });

    // Cierra las sugerencias si el usuario hace clic fuera del buscador.
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !caja.contains(e.target)) ocultar();
    });

    // Estado de carga en "Aplicar Filtros"
    const formFiltros = document.getElementById('formFiltros');
    const btnFiltros   = document.getElementById('btnAplicarFiltros');
    if (formFiltros && btnFiltros) {
        formFiltros.addEventListener('submit', function () {
            btnFiltros.disabled = true;
            btnFiltros.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Aplicando...';
        });
    }
})();
</script>
@endsection
