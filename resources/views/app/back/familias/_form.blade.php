@if($errors->any())
<div class="alert alert-danger mb-4">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
<div class="admin-card">
    <div class="admin-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-600">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                       value="{{ old('nombre', $familia->nombre ?? '') }}" required>
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-600">Ícono</label>
                @php
                    $opcionesIcono = [
                        'fas fa-spa'               => '🌸 Flor (Floral)',
                        'fas fa-tree'               => '🌲 Árbol (Maderado)',
                        'fas fa-star-and-crescent'  => '✨ Estrella (Oriental / Especiado)',
                        'fas fa-lemon'              => '🍋 Limón (Fresco / Cítrico)',
                        'fas fa-water'              => '🌊 Agua (Acuático / Marino)',
                        'fas fa-fire'               => '🔥 Fuego (Oud / Medio Oriente)',
                        'fas fa-candy-cane'         => '🍬 Dulce (Gourmand)',
                        'fas fa-gem'                => '💎 Joya (Nicho / Artesanal)',
                    ];
                    $iconoActual = old('icono', $familia->icono ?? 'fas fa-spa');
                @endphp
                <select name="icono" class="form-select">
                    @foreach($opcionesIcono as $valor => $etiqueta)
                    <option value="{{ $valor }}" {{ $iconoActual === $valor ? 'selected' : '' }}>
                        {{ $etiqueta }}
                    </option>
                    @endforeach
                </select>
                <small class="text-muted">Elige el ícono que mejor represente esta familia olfativa.</small>
            </div>
            <div class="col-12">
                <label class="form-label fw-600">Descripción</label>
                <textarea name="descripcion" rows="3" class="form-control">{{ old('descripcion', $familia->descripcion ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <div class="form-check form-switch mt-3">
                    <input type="hidden" name="activo" value="0">
                    <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo"
                           {{ old('activo', ($familia->activo ?? true)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">Activa (visible en tienda)</label>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-check form-switch mt-3">
                    <input type="hidden" name="es_premium" value="0">
                    <input class="form-check-input" type="checkbox" name="es_premium" value="1" id="es_premium"
                           {{ old('es_premium', ($familia->es_premium ?? false)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="es_premium">Familia premium</label>
                </div>
                <small class="text-muted">
                    Afecta el rango de precio sugerido en el formulario de fragancias
                    (las premium sugieren un precio por ml más alto).
                </small>
            </div>
        </div>
    </div>
</div>
