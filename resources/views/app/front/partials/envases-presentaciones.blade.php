<section class="envases-presentaciones py-5">
    <div class="container">
        <div class="envases-header text-center mb-4">
            <p class="eyebrow">Colección</p>
            <h2 class="section-title">Elige tu Presentación Ideal</h2>
            <p class="text-muted mb-0">Cada tamaño y categoría, en un solo vistazo.</p>
        </div>

        @php
            $envases = $envases ?? collect();
            $envasesPorTamano = $envases->groupBy('tamano');
            $sizes = array_keys(config('comercial.tamanos', []));
            $categories = config('comercial.envases.categorias', []);
            $iconos = ['hombre' => 'fa-mars', 'mujer' => 'fa-venus', 'unisex' => 'fa-venus-mars'];
        @endphp

        <div class="envases-size-switch" role="tablist">
            @foreach($sizes as $index => $size)
                <button class="envase-size-btn {{ $index === 0 ? 'active' : '' }}" id="envase-{{ $size }}-tab"
                        data-bs-toggle="tab" data-bs-target="#envase-{{ $size }}"
                        type="button" role="tab" aria-controls="envase-{{ $size }}"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                    {{ $size }} <span>ml</span>
                </button>
            @endforeach
        </div>

        <div class="tab-content">
            @foreach($sizes as $index => $size)
                @php($itemsForSize = $envasesPorTamano->get((string) $size) ?? collect())
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="envase-{{ $size }}" role="tabpanel" aria-labelledby="envase-{{ $size }}-tab">
                    <div class="row g-4">
                        @foreach($categories as $key => $label)
                            @php($item = $itemsForSize->firstWhere('categoria', $key) ?? null)
                            <div class="col-12 col-md-4">
                                <a href="{{ route('catalogo', ['envase' => $size, 'genero' => $key]) }}" class="envase-showcase-card">
                                    <div class="envase-showcase-media">
                                        @if($item && $item->imagen_url)
                                            <img src="{{ $item->imagen_url }}" alt="{{ $label }} {{ $size }} ml" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div class="envase-showcase-empty">
                                                <i class="fas fa-flask"></i>
                                            </div>
                                        @endif
                                        <span class="envase-showcase-size">{{ $size }} ml</span>
                                        <div class="envase-showcase-overlay">
                                            <span class="envase-showcase-icon"><i class="fas {{ $iconos[$key] ?? 'fa-spray-can' }}"></i></span>
                                            <span class="envase-showcase-label">{{ $label }}</span>
                                            <span class="envase-showcase-cta">Ver colección <i class="fas fa-arrow-right"></i></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
