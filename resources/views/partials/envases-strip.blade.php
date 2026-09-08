<section class="envases-strip py-5 border-top">
    <div class="container">
        <div class="envases-strip-header d-flex align-items-end justify-content-between flex-wrap gap-2">
            <div>
                <p class="eyebrow mb-0">Presentaciones</p>
                <h3 class="section-title mb-0">Envases y tamaños disponibles</h3>
                <p class="text-muted mb-0" style="font-size:.88rem">Elige la medida ideal para cada ocasión.</p>
            </div>
            <div class="envases-strip-note d-none d-md-block">
                <i class="fas fa-circle-check"></i>Disponible en todas las páginas
            </div>
        </div>

        @php
            $envases = $envases ?? collect();
            $envasesVisibles = $envases->take(3);
        @endphp

        <div class="d-flex gap-3 overflow-auto envases-row justify-content-center">
            @foreach($envasesVisibles as $env)
                @php
                    $iconosGenero = ['hombre' => 'fa-mars', 'mujer' => 'fa-venus', 'unisex' => 'fa-venus-mars'];
                    $iconoGenero = $iconosGenero[$env->categoria] ?? 'fa-spray-can';
                @endphp
                <div class="envase-item">
                    <a href="{{ route('catalogo', ['envase' => $env->tamano]) }}" class="envase-link" title="Ver fragancias {{ $env->tamano }} ml">
                        <div class="envase-card">
                            <div class="envase-bubble">
                                @if($env->imagen_url)
                                    <img src="{{ $env->imagen_url }}" alt="Envase {{ $env->tamano }} ml {{ $env->categoria }}" class="envase-img" loading="lazy"
                                         onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('i'),{className:'fas fa-flask'}));">
                                @elseif($env->icono)
                                    <i class="{{ $env->icono }} fa-lg"></i>
                                @else
                                    <i class="fas fa-flask"></i>
                                @endif
                                <span class="envase-size-badge">{{ $env->tamano }} ml</span>
                            </div>
                            <div class="envase-cat"><i class="fas {{ $iconoGenero }}"></i>{{ ucfirst($env->categoria ?? 'Standar') }}</div>
                            <div class="envase-count">{{ $env->productos_count ?? 0 }} {{ ($env->productos_count ?? 0) === 1 ? 'producto' : 'productos' }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
