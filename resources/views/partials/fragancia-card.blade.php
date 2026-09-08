<div class="fragancia-card h-100">
    <a href="{{ route('fragancia.show', $fragancia->slug) }}" class="fragancia-card-img-link">
        <img src="{{ $fragancia->imagenMostrar() }}"
             alt="{{ $fragancia->nombre }}" class="fragancia-card-img" loading="lazy"
             onerror="this.onerror=null;this.src='{{ asset('storage/fragancias/generic/frasco_nicho.png') }}';">
        @if(isset($badge))
            <span class="fragancia-badge">{{ $badge }}</span>
        @elseif(\App\Models\Configuracion::obtener('mostrar_stock_bajo_cliente', config('comercial.mostrar_stock_bajo_cliente', false)) && $fragancia->tieneStockBajo())
            <span class="fragancia-badge" style="background:#DC3545;">¡Solo quedan {{ $fragancia->stockTotal() }}!</span>
        @elseif($fragancia->tieneDescuento())
            <span class="fragancia-badge badge-oferta">OFERTA</span>
        @endif
    </a>
    <div class="fragancia-card-body">
        <div class="fragancia-card-casa">{{ $fragancia->casa_perfumista }}</div>
        <h5 class="fragancia-card-nombre">
            <a href="{{ route('fragancia.show', $fragancia->slug) }}">{{ $fragancia->nombre }}</a>
        </h5>
        <div class="fragancia-card-meta">
            <span>{{ $fragancia->etiquetaGenero() }}</span>
        </div>
        <div class="fragancia-card-precio d-flex align-items-center gap-2">
            @if($fragancia->tamanos->count() > 1)
                <span class="text-muted small">Desde</span>
            @endif
            <span class="precio-final">${{ number_format($fragancia->precioFinal(), 0, ',', '.') }}</span>
            @if($fragancia->tamanos->count() > 1 && $fragancia->tamanoMasBarato())
                <span class="text-muted small">({{ $fragancia->tamanoMasBarato()->tamano }})</span>
            @endif
            @if($fragancia->tamanos->count() <= 1 && $fragancia->tieneDescuento())
                <span class="precio-original">${{ number_format(round($fragancia->precio), 0, ',', '.') }}</span>
            @endif
        </div>
        <div class="fragancia-card-actions mt-2">
            <a href="{{ route('fragancia.show', $fragancia->slug) }}"
               class="btn btn-dark-perfume btn-sm w-100">Conocer Más</a>
        </div>
    </div>
</div>
