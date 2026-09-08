@if($errors->any())
<div class="alert alert-danger mb-4">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="row g-4">

    <div class="col-lg-8">

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Clasificación</h6></div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600">Familia Olfativa <span class="text-danger">*</span></label>
                        <select name="familia_id" class="form-select @error('familia_id') is-invalid @enderror" required>
                            <option value="">Seleccionar...</option>
                            @foreach($familias as $fam)
                            <option value="{{ $fam->id }}" data-premium="{{ $fam->es_premium ? 1 : 0 }}"
                                    data-descripcion="{{ str_replace('"', '&quot;', trim($fam->descripcion ?? '')) }}"
                                {{ old('familia_id', $fragancia->familia_id ?? '') == $fam->id ? 'selected' : '' }}>
                                {{ $fam->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('familia_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Género <span class="text-danger">*</span></label>
                        <select name="genero" class="form-select @error('genero') is-invalid @enderror" required>
                            <option value="">Seleccionar...</option>
                            @foreach(['mujer' => 'Mujer', 'hombre' => 'Hombre', 'unisex' => 'Unisex'] as $valor => $label)
                                <option value="{{ $valor }}" {{ old('genero', $fragancia->genero ?? '') == $valor ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('genero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <small class="text-muted d-block mt-2">Empieza por aquí: elige la familia olfativa y el género para generar la descripción y las notas.</small>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Información Principal</h6></div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-600">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $fragancia->nombre ?? '') }}" required>
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Casa Perfumista <span class="text-danger">*</span></label>
                        <input type="text" name="casa_perfumista" list="listaCasas"
                               class="form-control @error('casa_perfumista') is-invalid @enderror"
                               value="{{ old('casa_perfumista', $fragancia->casa_perfumista ?? 'Ambar') }}" required>
                        <datalist id="listaCasas">
                            @foreach($casasExistentes ?? [] as $casa)
                            <option value="{{ $casa }}"></option>
                            @endforeach
                        </datalist>
                        @error('casa_perfumista')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label fw-600 mb-1">Descripción <span class="text-danger">*</span></label>
                            <button type="button" id="btnGenerarDescripcion" class="btn btn-sm btn-outline-primary mb-1">
                                ✨ Generar automáticamente
                            </button>
                        </div>
                        <textarea id="descripcionInput" name="descripcion" rows="4"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  required>{{ old('descripcion', $fragancia->descripcion ?? '') }}</textarea>
                        @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Elige la familia olfativa y (opcional) las notas, luego pulsa "Generar automáticamente" para no escribirla a mano.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Notas Olfativas (Pirámide)</h6></div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">🌿 Notas de Salida</label>
                        <input type="text" id="notasSalidaInput" name="notas_salida" class="form-control"
                               list="listaNotasSalida" placeholder="Bergamota, Limón..."
                               value="{{ old('notas_salida', $fragancia->notas_salida ?? '') }}">
                        <datalist id="listaNotasSalida">
                            @foreach(['Bergamota','Limón','Mandarina','Pomelo','Pimienta rosa','Pimienta negra','Cardamomo','Azafrán','Nota marina','Manzana verde'] as $n)
                            <option value="{{ $n }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">🌸 Notas de Corazón</label>
                        <input type="text" id="notasCorazonInput" name="notas_corazon" class="form-control"
                               list="listaNotasCorazon" placeholder="Rosa, Jazmín..."
                               value="{{ old('notas_corazon', $fragancia->notas_corazon ?? '') }}">
                        <datalist id="listaNotasCorazon">
                            @foreach(['Rosa','Jazmín','Peonía','Iris','Lavanda','Geranio','Incienso','Té verde','Oud','Vainilla'] as $n)
                            <option value="{{ $n }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">🌳 Notas de Fondo</label>
                        <input type="text" id="notasFondoInput" name="notas_fondo" class="form-control"
                               list="listaNotasFondo" placeholder="Sándalo, Almizcle..."
                               value="{{ old('notas_fondo', $fragancia->notas_fondo ?? '') }}">
                        <datalist id="listaNotasFondo">
                            @foreach(['Sándalo','Almizcle','Ámbar','Vetiver','Cedro','Cuero','Pachulí','Tonka','Musgo de roble'] as $n)
                            <option value="{{ $n }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">Escribe las notas si quieres, o usa "Generar automáticamente" para que el sistema te sugiera una descripción acorde a la familia.</small>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="mb-0">Precio y Stock por Tamaño</h6>
            </div>
            <div class="admin-card-body">
                <small class="text-muted d-block mb-3">
                    Escribe el precio de venta por ml. El sistema calcula automáticamente los tamaños 100/50/30 ml.
                </small>

                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label small mb-1">Precio por ml (USD)</label>
                            <button type="button" id="btnSugerirPrecio" class="btn btn-sm btn-outline-primary mb-1">
                                ✨ Sugerir por familia
                            </button>
                        </div>
                        <input type="number" name="precio_por_ml" id="precioPorMlInput" step="0.0001" min="0"
                               class="form-control @error('precio_por_ml') is-invalid @enderror"
                               value="{{ old('precio_por_ml', (isset($fragancia) ? $fragancia->precio_por_ml : null) ?? '') }}" required>
                        @error('precio_por_ml')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Precio especial por ml (opcional)</label>
                        <input type="number" name="precio_especial_por_ml" id="precioEspecialPorMlInput" step="0.0001" min="0"
                               class="form-control @error('precio_especial_por_ml') is-invalid @enderror"
                               value="{{ old('precio_especial_por_ml', (isset($fragancia) ? $fragancia->precio_especial_por_ml : null) ?? '') }}">
                        @error('precio_especial_por_ml')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Costo de elaboración por ml (USD)</label>
                        <input type="number" name="costo_por_ml" id="costoPorMlInput" step="0.0001" min="0"
                               class="form-control @error('costo_por_ml') is-invalid @enderror"
                               value="{{ old('costo_por_ml', (isset($fragancia) ? $fragancia->costo_por_ml : null) ?? '') }}">
                        @error('costo_por_ml')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Costo real de elaboración por ml. Se usa solo internamente para controlar margen y rentabilidad.</small>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="button" id="btnCalcularTodo" class="btn btn-success">
                            🧮 Calcular
                        </button>
                    </div>
                </div>

                <div class="admin-card border-0 shadow-sm mb-3" style="background:linear-gradient(180deg,#fffdf9 0%, #f8f3ee 100%);">
                    <div class="admin-card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <strong class="d-block">Estimado por presentación</strong>
                                <small class="text-muted">Costo de elaboración + envase + margen bruto. El IVA se añade al precio final.</small>
                            </div>
                            <span class="badge rounded-pill bg-light text-dark border">Vista previa</span>
                        </div>
                        <div id="previewTamanos" class="row g-2"></div>
                    </div>
                </div>

                @php
                    $tamanosPorMl = ($fragancia ?? null)?->tamanos?->keyBy(fn($t) => $t->ml()) ?? collect();
                @endphp
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Stock 100 ml</label>
                        <input type="number" name="stock_100" min="0"
                               class="form-control @error('stock_100') is-invalid @enderror"
                               value="{{ old('stock_100', $tamanosPorMl[100]->stock ?? 0) }}" required>
                        @error('stock_100')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Stock 50 ml</label>
                        <input type="number" name="stock_50" min="0"
                               class="form-control @error('stock_50') is-invalid @enderror"
                               value="{{ old('stock_50', $tamanosPorMl[50]->stock ?? 0) }}" required>
                        @error('stock_50')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Stock 30 ml</label>
                        <input type="number" name="stock_30" min="0"
                               class="form-control @error('stock_30') is-invalid @enderror"
                               value="{{ old('stock_30', $tamanosPorMl[30]->stock ?? 0) }}" required>
                        @error('stock_30')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Imagen Principal</h6></div>
            <div class="admin-card-body">
                <img id="previewImagenActual"
                     src="{{ isset($fragancia) ? $fragancia->imagenMostrar() : '' }}"
                     class="img-fluid rounded mb-2 {{ isset($fragancia) ? '' : 'd-none' }}" alt="Vista previa">
                <input type="file" name="imagen_principal" id="imagenPrincipalInput" class="form-control" accept="image/*">
                <small class="text-muted d-block mb-2">JPG, PNG. Máx 2MB. Recomendado: 800×800px</small>
                <small id="imagenPrincipalError" class="text-danger d-none"></small>

                <input type="hidden" name="imagen_generica" id="imagenGenericaInput" value="">
                <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                    <small class="text-muted mb-0">¿Todavía no tienes foto? Usa el frasco genérico según la familia olfativa:</small>
                </div>
                <div class="d-grid">
                    <button type="button" id="btnUsarGenerica" class="btn btn-sm btn-outline-secondary">
                        🧴 Usar frasco genérico de la familia seleccionada
                    </button>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header"><h6 class="mb-0">Visibilidad</h6></div>
            <div class="admin-card-body">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="activo"
                           id="activo" value="1"
                           {{ old('activo', ($fragancia ?? null)?->activo) ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">Activo (visible en tienda)</label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // Inyectados desde config/comercial.php — única fuente de verdad,
    // el backend usa exactamente estos mismos valores para calcular
    // precios y recargos reales.
    const SUGERENCIA_PRECIO = @json(config('comercial.sugerencia_precio'));
    const RECARGOS = @json($recargos ?? collect(config('comercial.tamanos'))->map(fn($c) => $c['recargo']));
    const COSTOS_ENVASE = @json($costosEnvase ?? collect(config('comercial.tamanos'))->map(fn() => 0));
    // Configuración del margen de ganancia, editable en Admin > Configuración
    // (no en .env): modo "porcentaje" o "valor_fijo" (monto fijo sobre el
    // frasco de 100 ml), más el IVA vigente para desglosar el precio final.
    const MARGEN = @json($margenObjetivo ?? ['modo' => 'porcentaje', 'porcentaje' => 40, 'valor_fijo' => 8]);
    const IVA_PORCENTAJE = @json((float) ($ivaPorcentaje ?? 0));
    const IVA_INCLUIDO = @json((bool) ($ivaIncluido ?? false));

    // Funciones auxiliares para descripción (definidas aquí para disponibilidad global en event listeners)
    const first = (str) => (str || '').split(',')[0].trim().toLowerCase();
    const pickRandom = (arr) => arr[Math.floor(Math.random() * arr.length)];

    // Mapa: nombre exacto de familia (tal como está en la BD) -> adjetivo + banco de notas + slug de imagen genérica.
    // Si agregas una familia nueva en el seeder, agrégala también aquí.
    const FAMILIAS = {
        'Floral':               { slug: 'floral',   adj: 'floral y delicada',                 salida: ['Bergamota','Mandarina','Pimienta rosa'], corazon: ['Rosa','Jazmín','Peonía'], fondo: ['Sándalo','Almizcle blanco','Ámbar suave'] },
        'Maderado':             { slug: 'maderado', adj: 'amaderada y elegante',              salida: ['Bergamota','Pimienta negra','Manzana verde'], corazon: ['Lavanda','Cedro','Salvia'], fondo: ['Sándalo','Vetiver','Cuero'] },
        'Oriental / Especiado': { slug: 'oriental', adj: 'oriental y envolvente',             salida: ['Canela','Cardamomo','Azafrán'], corazon: ['Incienso','Clavo de olor','Rosa turca'], fondo: ['Ámbar','Vainilla','Benjuí'] },
        'Fresco / Cítrico':     { slug: 'fresco',   adj: 'fresca y energizante',              salida: ['Limón','Menta','Bergamota'], corazon: ['Té verde','Romero','Flor de azahar'], fondo: ['Almizcle claro','Cedro','Musgo'] },
        'Acuático / Marino':    { slug: 'acuatico', adj: 'acuática y ligera',                  salida: ['Nota marina','Bergamota','Sal marina'], corazon: ['Flor de loto','Pepino','Jazmín acuático'], fondo: ['Almizcle marino','Ámbar claro','Madera flotante'] },
        'Oud / Medio Oriente':  { slug: 'oud',      adj: 'intensa y lujosa, con carácter árabe', salida: ['Azafrán','Cardamomo','Rosa de damasco'], corazon: ['Oud','Cuero','Incienso'], fondo: ['Agarwood','Ámbar oscuro','Pachulí'] },
        'Gourmand':             { slug: 'gourmand', adj: 'dulce y gourmand',                  salida: ['Pera caramelizada','Mandarina','Café'], corazon: ['Vainilla','Praliné','Canela'], fondo: ['Tonka','Caramelo','Sándalo cremoso'] },
        'Nicho / Artesanal':    { slug: 'nicho',    adj: 'artesanal y distintiva',            salida: ['Bergamota rara','Elemi','Cardamomo negro'], corazon: ['Iris','Oud','Rosa oscura'], fondo: ['Ámbar gris','Vetiver','Cuero suave'] },
    };

    document.getElementById('btnSugerirPrecio')?.addEventListener('click', function () {
        const familiaSelect = document.querySelector('select[name="familia_id"]');
        const familiaNombre = familiaSelect?.selectedOptions?.[0]?.text?.trim();

        if (!FAMILIAS[familiaNombre]) {
            alert('Primero elige una familia olfativa (arriba, en Clasificación).');
            return;
        }

        // Premium ahora viene del flag "es_premium" de la familia (editable en
        // Admin > Familias), no de comparar el nombre por texto.
        const opcionSeleccionada = familiaSelect.selectedOptions[0];
        const esPremium = opcionSeleccionada?.dataset?.premium === '1';
        const rango = esPremium ? SUGERENCIA_PRECIO.premium : SUGERENCIA_PRECIO.normal;
        const min = rango.min;
        const max = rango.max;

        const precioInput = document.getElementById('precioPorMlInput');
        if (precioInput && precioInput.value.trim() === '') {
            precioInput.value = (min + Math.random() * (max - min)).toFixed(2);
            precioInput.dispatchEvent(new Event('input'));
        }
    });

    document.getElementById('imagenPrincipalInput')?.addEventListener('change', function () {
        const errorEl = document.getElementById('imagenPrincipalError');
        const preview = document.getElementById('previewImagenActual');
        errorEl.classList.add('d-none');

        if (!this.files || !this.files.length) return;

        const archivo = this.files[0];
        const MAX_BYTES = 2 * 1024 * 1024; // 2MB, igual al límite validado en el servidor

        if (archivo.size > MAX_BYTES) {
            errorEl.textContent = 'La imagen pesa ' + (archivo.size / 1024 / 1024).toFixed(1) + 'MB; el máximo permitido es 2MB.';
            errorEl.classList.remove('d-none');
            this.value = '';
            return;
        }

        document.getElementById('imagenGenericaInput').value = '';

        // Preview en vivo: sin esto, el admin no ve la foto que acaba de elegir
        // hasta guardar y recargar la página.
        const lector = new FileReader();
        lector.onload = function (e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        lector.readAsDataURL(archivo);
    });

    document.getElementById('btnUsarGenerica')?.addEventListener('click', function () {
        const familiaSelect = document.querySelector('select[name="familia_id"]');
        const familiaNombre = familiaSelect?.selectedOptions?.[0]?.text?.trim();
        const datos = FAMILIAS[familiaNombre];

        if (!datos) {
            alert('Primero elige una familia olfativa (arriba, en Clasificación).');
            return;
        }

        document.getElementById('imagenGenericaInput').value = datos.slug;
        document.getElementById('imagenPrincipalInput').value = '';

        const preview = document.getElementById('previewImagenActual');
        preview.src = '/storage/fragancias/generic/frasco_' + datos.slug + '.png';
        preview.classList.remove('d-none');
    });

    function formatearUsd(valor) {
        return '$' + (Number.isFinite(valor) ? valor.toFixed(2) : '0.00');
    }

    function desglosarIva(montoBase) {
        const porcentaje = Number(IVA_PORCENTAJE) || 0;
        const monto = Number(montoBase) || 0;

        if (IVA_INCLUIDO) {
            const total = monto;
            const base = porcentaje > 0 ? total / (1 + (porcentaje / 100)) : total;
            const iva = total - base;
            return {
                base: Number(base.toFixed(2)),
                iva: Number(iva.toFixed(2)),
                conIva: Number(total.toFixed(2)),
                porcentaje,
                incluido: true,
            };
        }

        const base = monto;
        const iva = monto * (porcentaje / 100);
        const conIva = base + iva;
        return {
            base: Number(base.toFixed(2)),
            iva: Number(iva.toFixed(2)),
            conIva: Number(conIva.toFixed(2)),
            porcentaje,
            incluido: false,
        };
    }

    function actualizarPreview() {
        const cont = document.getElementById('previewTamanos');
        if (!cont) return;

        const precioPorMl = parseFloat(document.getElementById('precioPorMlInput')?.value) || 0;
        const costoPorMl = parseFloat(document.getElementById('costoPorMlInput')?.value) || 0;
        const genero = 'unisex';

        if (precioPorMl <= 0) {
            cont.innerHTML = `
                <div class="col-12">
                    <div class="small text-muted rounded-3 border p-3 bg-white">
                        Ingresa el precio por ml para ver el cálculo de cada presentación.
                    </div>
                </div>
            `;
            return;
        }

        const filas = Object.entries(RECARGOS).map(([ml, recargo]) => {
            const mlNum = Number(ml);
            const recargoPct = Number(recargo) * 100;
            const costoEnvase = parseFloat(COSTOS_ENVASE[`${ml}|${genero}`]) || 0;
            const costoProduccion = (costoPorMl * mlNum) + costoEnvase;
            const precioConRecargo = precioPorMl * mlNum * (1 + Number(recargo));
            const desglose = desglosarIva(precioConRecargo);
            const precioFinal = desglose.conIva;
            const margenBruto = precioConRecargo - costoProduccion;  // Margen SIN IVA
            const clase = margenBruto >= 0 ? 'text-success' : 'text-danger';

            return `
                <div class="col-md-4">
                    <div class="rounded-3 border bg-white p-3 h-100 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>${ml} ml</strong>
                            <span class="badge rounded-pill bg-light text-dark">${recargoPct.toFixed(0)}% recargo</span>
                        </div>

                        <div class="d-grid gap-2">
                            <div class="rounded-2 border bg-light px-2 py-1 small">
                                <div class="text-muted">Costo producción</div>
                                <div class="fw-semibold">${formatearUsd(costoProduccion)}</div>
                            </div>
                            <div class="rounded-2 border bg-light px-2 py-1 small">
                                <div class="text-muted">Margen bruto</div>
                                <div class="fw-semibold ${clase}">${formatearUsd(margenBruto)}</div>
                            </div>
                            <div class="rounded-2 border bg-light px-2 py-1 small">
                                <div class="text-muted">IVA</div>
                                <div class="fw-semibold">${formatearUsd(desglose.iva)}</div>
                            </div>
                            <div class="rounded-2 border bg-success-subtle px-2 py-1 small">
                                <div class="text-success">Precio final</div>
                                <div class="fw-bold text-success fs-6">${formatearUsd(precioFinal)}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        cont.innerHTML = filas;
    }

    // Redondea al múltiplo configurado en Admin > Configuración (MARGEN.redondeo),
    // para que el precio final salga en un valor "cerrado" (ej. $35, $40)
    // en vez de $34.83.
    function redondearCerrado(valor, incremento) {
        const inc = incremento > 0 ? incremento : 1;
        return Math.round(valor / inc) * inc;
    }

    document.getElementById('precioPorMlInput')?.addEventListener('input', () => { actualizarPreview(); });
    document.getElementById('costoPorMlInput')?.addEventListener('input', () => { });
    document.getElementById('btnCalcularTodo')?.addEventListener('click', () => { actualizarPreview(); });
    actualizarPreview();
    actualizarVisibilidadModoMargen();
    actualizarPreviewGanancia();

    document.getElementById('btnGenerarDescripcion')?.addEventListener('click', function () {
        const familiaSelect = document.querySelector('select[name="familia_id"]');
        const familiaOption = familiaSelect?.selectedOptions?.[0];
        const familiaNombre = familiaOption?.text?.trim();
        const datos = FAMILIAS[familiaNombre] || {};
        const descripcionFamilia = (familiaOption?.dataset?.descripcion || '').trim();

        if (!familiaNombre) {
            alert('Primero elige una familia olfativa (arriba, en Clasificación) para poder generar la descripción.');
            return;
        }

        const salidaInput  = document.getElementById('notasSalidaInput');
        const corazonInput = document.getElementById('notasCorazonInput');
        const fondoInput   = document.getElementById('notasFondoInput');

        if (!salidaInput.value.trim())  salidaInput.value  = (datos.salida || []).join(', ');
        if (!corazonInput.value.trim()) corazonInput.value = (datos.corazon || []).join(', ');
        if (!fondoInput.value.trim())   fondoInput.value   = (datos.fondo || []).join(', ');

        const salida  = first(salidaInput.value)  || pickRandom(datos.salida || ['bergamota']).toLowerCase();
        const corazon = first(corazonInput.value) || pickRandom(datos.corazon || ['rosa']).toLowerCase();
        const fondo   = first(fondoInput.value)   || pickRandom(datos.fondo || ['sándalo']).toLowerCase();

        const descripcionBase = (descripcionFamilia
            ? descripcionFamilia.replace(/\s+/g, ' ').trim()
            : (datos.adj ? `Una fragancia ${datos.adj}` : `Una fragancia de la familia ${familiaNombre}`))
            .replace(/[.\s]+$/, ''); // quita el punto final para no duplicarlo al concatenar abajo

        const plantillas = [
            `${descripcionBase}. Se abre con ${salida}, se despliega en ${corazon} y cierra con un fondo de ${fondo}.`,
            `${descripcionBase} que comienza con ${salida}, sigue con ${corazon} y termina con ${fondo} en la piel.`,
            `Composición inspirada en ${familiaNombre}: ${descripcionBase.toLowerCase()} con salida en ${salida}, corazón de ${corazon} y base de ${fondo}.`,
        ];

        const descripcionInput = document.getElementById('descripcionInput');
        const nuevaDescripcion = pickRandom(plantillas);

        if (descripcionInput.value.trim()) {
            confirmarAccion({
                titulo: 'Reemplazar descripción',
                mensaje: 'Ya hay una descripción escrita. ¿Reemplazarla por la generada automáticamente?',
                textoBoton: 'Reemplazar',
                variante: 'primary',
                onConfirmar: function () { descripcionInput.value = nuevaDescripcion; }
            });
            return;
        }
        descripcionInput.value = nuevaDescripcion;
    });

    // Cargar automáticamente la descripción de la familia cuando se selecciona
    document.querySelector('select[name="familia_id"]')?.addEventListener('change', function () {
        const familiaSelect = this;
        const familiaOption = familiaSelect.selectedOptions?.[0];
        const familiaNombre = familiaOption?.text?.trim();
        const datos = FAMILIAS[familiaNombre] || {};
        const descripcionFamilia = (familiaOption?.dataset?.descripcion || '').trim();
        const descripcionInput = document.getElementById('descripcionInput');

        if (!descripcionInput.value.trim()) {
            // Si está vacío, generar automáticamente una descripción completa
            const salidaInput  = document.getElementById('notasSalidaInput');
            const corazonInput = document.getElementById('notasCorazonInput');
            const fondoInput   = document.getElementById('notasFondoInput');

            // Llenar notas si están vacías
            if (!salidaInput.value.trim())  salidaInput.value  = (datos.salida || []).join(', ');
            if (!corazonInput.value.trim()) corazonInput.value = (datos.corazon || []).join(', ');
            if (!fondoInput.value.trim())   fondoInput.value   = (datos.fondo || []).join(', ');

            // Generar descripción completa
            const salida  = first(salidaInput.value)  || pickRandom(datos.salida || ['bergamota']).toLowerCase();
            const corazon = first(corazonInput.value) || pickRandom(datos.corazon || ['rosa']).toLowerCase();
            const fondo   = first(fondoInput.value)   || pickRandom(datos.fondo || ['sándalo']).toLowerCase();

            const descripcionBase = (descripcionFamilia
                ? descripcionFamilia.replace(/\s+/g, ' ').trim()
                : (datos.adj ? `Una fragancia ${datos.adj}` : `Una fragancia de la familia ${familiaNombre}`))
                .replace(/[.\s]+$/, '');

            const plantillas = [
                `${descripcionBase}. Se abre con ${salida}, se despliega en ${corazon} y cierra con un fondo de ${fondo}.`,
                `${descripcionBase} que comienza con ${salida}, sigue con ${corazon} y termina con ${fondo} en la piel.`,
                `Composición inspirada en ${familiaNombre}: ${descripcionBase.toLowerCase()} con salida en ${salida}, corazón de ${corazon} y base de ${fondo}.`,
            ];

            descripcionInput.value = pickRandom(plantillas);
        }
    });

    // Generar descripción al cargar la página si hay una familia preseleccionada
    const familiaSelect = document.querySelector('select[name="familia_id"]');
    if (familiaSelect?.value) {
        const familiaOption = familiaSelect.selectedOptions?.[0];
        const familiaNombre = familiaOption?.text?.trim();
        const datos = FAMILIAS[familiaNombre] || {};
        const descripcionFamilia = (familiaOption?.dataset?.descripcion || '').trim();
        const descripcionInput = document.getElementById('descripcionInput');

        if (!descripcionInput.value.trim()) {
            // Generar descripción completa automáticamente
            const salidaInput  = document.getElementById('notasSalidaInput');
            const corazonInput = document.getElementById('notasCorazonInput');
            const fondoInput   = document.getElementById('notasFondoInput');

            // Llenar notas si están vacías
            if (!salidaInput.value.trim())  salidaInput.value  = (datos.salida || []).join(', ');
            if (!corazonInput.value.trim()) corazonInput.value = (datos.corazon || []).join(', ');
            if (!fondoInput.value.trim())   fondoInput.value   = (datos.fondo || []).join(', ');

            // Generar descripción completa
            const salida  = first(salidaInput.value)  || pickRandom(datos.salida || ['bergamota']).toLowerCase();
            const corazon = first(corazonInput.value) || pickRandom(datos.corazon || ['rosa']).toLowerCase();
            const fondo   = first(fondoInput.value)   || pickRandom(datos.fondo || ['sándalo']).toLowerCase();

            const descripcionBase = (descripcionFamilia
                ? descripcionFamilia.replace(/\s+/g, ' ').trim()
                : (datos.adj ? `Una fragancia ${datos.adj}` : `Una fragancia de la familia ${familiaNombre}`))
                .replace(/[.\s]+$/, '');

            const plantillas = [
                `${descripcionBase}. Se abre con ${salida}, se despliega en ${corazon} y cierra con un fondo de ${fondo}.`,
                `${descripcionBase} que comienza con ${salida}, sigue con ${corazon} y termina con ${fondo} en la piel.`,
                `Composición inspirada en ${familiaNombre}: ${descripcionBase.toLowerCase()} con salida en ${salida}, corazón de ${corazon} y base de ${fondo}.`,
            ];

            descripcionInput.value = pickRandom(plantillas);
        }
    }
})();
</script>
