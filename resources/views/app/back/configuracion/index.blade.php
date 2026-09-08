@extends('layouts.template-back')
@section('title', 'Configuración')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Configuración del Sitio</h3>
            <p class="text-muted mb-0">Ajustes generales de la tienda, editables sin tocar código.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Un solo form: todas las secciones se guardan juntas con "Guardar cambios". --}}
    <form action="{{ route('admin.configuracion.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Envío</h6></div>
            <div class="admin-card-body">
                <small class="text-muted d-block mb-3">
                    La tienda despacha únicamente a nivel <strong>nacional</strong>.
                </small>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-600">Envío gratis desde (USD)</label>
                        <input type="number" name="envio_gratis_desde" step="0.01" min="0"
                               class="form-control @error('envio_gratis_desde') is-invalid @enderror"
                               value="{{ old('envio_gratis_desde', $envioGratisDesde) }}" required>
                        @error('envio_gratis_desde')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Subtotal a partir del cual el envío es gratis.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Costo de envío nacional (USD)</label>
                        <input type="number" name="costo_envio_nacional" step="0.01" min="0"
                               class="form-control @error('costo_envio_nacional') is-invalid @enderror"
                               value="{{ old('costo_envio_nacional', $costoEnvioNacional) }}" required>
                        @error('costo_envio_nacional')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Este es el que se cobra actualmente en el checkout.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Costo REAL de envío por pedido (USD)</label>
                        <input type="number" name="costo_envio_real" step="0.01" min="0"
                               class="form-control @error('costo_envio_real') is-invalid @enderror"
                               value="{{ old('costo_envio_real', $costoEnvioReal) }}" required>
                        @error('costo_envio_real')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">
                            Lo que le pagas de verdad al courier por pedido despachado (puede ser distinto a lo
                            que cobras, sobre todo con envío "gratis"). Solo se usa para descontarlo de la
                            ganancia en Reportes; nunca se cobra al cliente.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Sección "Más Vendidas" (inicio)</h6></div>
            <div class="admin-card-body">
                <small class="text-muted d-block mb-3">
                    Esta sección se calcula sola a partir de las ventas reales registradas
                    (no es un checkbox manual). Aquí solo controlas el título; siempre se muestran
                    las 3 fragancias más vendidas.
                </small>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-600">Título de la sección</label>
                        <input type="text" name="top_ventas_titulo" maxlength="60"
                               class="form-control @error('top_ventas_titulo') is-invalid @enderror"
                               value="{{ old('top_ventas_titulo', $topVentasTitulo) }}" required>
                        @error('top_ventas_titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Inventario</h6></div>
            <div class="admin-card-body">
                <small class="text-muted d-block mb-3">
                    Nivel de stock a partir del cual una fragancia se marca como "stock urgente"
                    (chip amarillo en el listado, aviso en la ficha de producto y alerta por correo al equipo).
                </small>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-600">Umbral de stock bajo (unidades)</label>
                        <input type="number" name="stock_bajo_umbral" step="1" min="0"
                               class="form-control @error('stock_bajo_umbral') is-invalid @enderror"
                               value="{{ old('stock_bajo_umbral', $stockBajoUmbral) }}" required>
                        @error('stock_bajo_umbral')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="mostrar_stock_bajo_cliente"
                                   name="mostrar_stock_bajo_cliente" value="1"
                                   @if(old('mostrar_stock_bajo_cliente', $mostrarStockBajoCliente)) checked @endif>
                            <label class="form-check-label" for="mostrar_stock_bajo_cliente">
                                Mostrarle al cliente avisos de "¡solo quedan X!" en la tienda
                            </label>
                            <small class="text-muted d-block">
                                Apagado por defecto: el umbral de arriba solo dispara las alertas internas
                                (panel admin + correo al equipo). Si lo activas, el cliente también verá
                                cuántas unidades quedan cuando el stock esté bajo o agotado.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Cuenta bancaria para transferencias</h6></div>
            <div class="admin-card-body">
                <small class="text-muted d-block mb-3">
                    Estos datos se muestran al cliente en la confirmación del pedido cuando
                    elige pagar por transferencia. Antes solo se podían cambiar editando el
                    archivo <code>.env</code> del servidor.
                </small>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600">Banco</label>
                        <input type="text" name="banco_nombre" maxlength="100"
                               class="form-control @error('banco_nombre') is-invalid @enderror"
                               value="{{ old('banco_nombre', $bancoNombre) }}" required>
                        @error('banco_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Tipo de cuenta</label>
                        <input type="text" name="banco_tipo" maxlength="60"
                               class="form-control @error('banco_tipo') is-invalid @enderror"
                               value="{{ old('banco_tipo', $bancoTipo) }}" required>
                        @error('banco_tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Número de cuenta</label>
                        <input type="text" name="banco_numero" maxlength="40"
                               class="form-control @error('banco_numero') is-invalid @enderror"
                               value="{{ old('banco_numero', $bancoNumero) }}" required>
                        @error('banco_numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Titular</label>
                        <input type="text" name="banco_titular" maxlength="150"
                               class="form-control @error('banco_titular') is-invalid @enderror"
                               value="{{ old('banco_titular', $bancoTitular) }}" required>
                        @error('banco_titular')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">RUC</label>
                        <input type="text" name="banco_ruc" maxlength="20"
                               class="form-control @error('banco_ruc') is-invalid @enderror"
                               value="{{ old('banco_ruc', $bancoRuc) }}" required>
                        @error('banco_ruc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Impuestos (IVA)</h6></div>
            <div class="admin-card-body">
                <small class="text-muted d-block mb-3">
                    Configura el porcentaje de IVA aplicado a los productos y si los precios mostrados
                    ya incluyen el impuesto.
                </small>

                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label fw-600">IVA (%)</label>
                        <input type="number" name="iva_porcentaje" step="0.01" min="0" max="100"
                               class="form-control @error('iva_porcentaje') is-invalid @enderror"
                               value="{{ old('iva_porcentaje', $ivaPorcentaje) }}" required>
                        @error('iva_porcentaje')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600 d-block">Precios incluyen IVA</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="iva_incluido"
                                   name="iva_incluido" value="1" @if(old('iva_incluido', $ivaIncluido)) checked @endif>
                            <label class="form-check-label" for="iva_incluido">Sí — los precios muestran IVA</label>
                        </div>
                        @error('iva_incluido')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Margen de ganancia (precio recomendado)</h6></div>
            <div class="admin-card-body">
                <small class="text-muted d-block mb-3">
                    Este margen es el que usa el botón "💰 Recomendar precio con ganancia" en el
                    formulario de fragancias, para calcular un precio de venta sugerido a partir
                    del costo (elaboración + envase). Elige cómo lo quieres calcular:
                </small>

                <div class="row g-3 align-items-start">
                    <div class="col-md-12">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="margen_modo" id="margenModoPorcentaje"
                                   value="porcentaje" @if(old('margen_modo', $margenObjetivo['modo']) === 'porcentaje') checked @endif>
                            <label class="form-check-label" for="margenModoPorcentaje">Por porcentaje de ganancia</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="margen_modo" id="margenModoValorFijo"
                                   value="valor_fijo" @if(old('margen_modo', $margenObjetivo['modo']) === 'valor_fijo') checked @endif>
                            <label class="form-check-label" for="margenModoValorFijo">Por valor cerrado (monto fijo)</label>
                        </div>
                        @error('margen_modo')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Ganancia (%)</label>
                        <input type="number" name="margen_porcentaje" step="0.01" min="0" max="95"
                               class="form-control @error('margen_porcentaje') is-invalid @enderror"
                               value="{{ old('margen_porcentaje', $margenObjetivo['porcentaje']) }}" required>
                        @error('margen_porcentaje')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Ej.: 40 = el precio de venta deja un 40% de ganancia sobre el costo.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Monto fijo (USD, sobre frasco de 100 ml)</label>
                        <input type="number" name="margen_valor_fijo" step="0.01" min="0"
                               class="form-control @error('margen_valor_fijo') is-invalid @enderror"
                               value="{{ old('margen_valor_fijo', $margenObjetivo['valor_fijo']) }}" required>
                        @error('margen_valor_fijo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Ej.: 8 = se suman $8 al costo del frasco de 100 ml, sin importar cuál sea ese costo.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Redondear precio final a</label>
                        <select name="margen_redondeo" class="form-select @error('margen_redondeo') is-invalid @enderror" required>
                            @foreach(['0.25' => '$0.25', '0.5' => '$0.50', '1' => '$1 (dólar entero)', '5' => '$5', '10' => '$10'] as $valor => $etiqueta)
                            <option value="{{ $valor }}" @if((string) old('margen_redondeo', $margenObjetivo['redondeo']) === $valor) selected @endif>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                        @error('margen_redondeo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">El precio sugerido del frasco de 100 ml se ajusta a este múltiplo, para que salga en un valor cerrado (ej. $35 en vez de $34.83).</small>
                    </div>
                </div>

                <small class="text-muted d-block mt-3">
                    Si arriba, en "Impuestos (IVA)", tienes activado <strong>"Precios incluyen IVA"</strong>,
                    el precio recomendado se calcula ya con el IVA sumado, y el formulario de la
                    fragancia te muestra el desglose (precio sin IVA + IVA = precio final).
                </small>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Recargo por tamaño de presentación</h6></div>
            <div class="admin-card-body">
                <small class="text-muted d-block mb-3">
                    Porcentaje que se suma al precio por ml para cada tamaño de frasco
                    (los frascos chicos cuestan más producir por unidad). Aplica igual
                    para todo el catálogo, no producto por producto. Si un cambio deja
                    algún producto con margen negativo (considerando costo de elaboración
                    + costo de envase), se guarda igual pero te avisamos arriba para que lo revises.
                </small>

                <div class="row g-3">
                    @foreach($recargosTamanos as $ml => $porcentaje)
                    <div class="col-md-4">
                        <label class="form-label fw-600">{{ $ml }} ml (%)</label>
                        <input type="number" name="recargo[{{ $ml }}]" step="0.01" min="0" max="500"
                               class="form-control @error('recargo.' . $ml) is-invalid @enderror"
                               value="{{ old('recargo.' . $ml, $porcentaje) }}" required>
                        @error('recargo.' . $ml)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h6 class="mb-0">Costo de envase</h6></div>
            <div class="admin-card-body">
                <p class="mb-0">
                    El costo del envase (vidrio, tapa, atomizador, caja) ahora se configura
                    por tamaño <strong>y categoría</strong> (hombre/mujer/unisex), junto con
                    la imagen de cada envase, en
                    <a href="{{ route('admin.envases.index') }}">Admin → Presentaciones de Envases</a>.
                </p>
            </div>
        </div>

        <p class="small text-muted mb-2">
                    Nota: el costo de envase <strong>no afecta el precio de venta</strong>
                    (eso solo depende del recargo % de arriba) — solo se usa para calcular tu
                    margen real. Cambiar el recargo % tampoco actualiza solo los precios ya
                    guardados de las 634 fragancias — solo aplica a productos nuevos o que
                    edites después. Si quieres recalcular ahora mismo el precio de TODO el
                    catálogo con el recargo vigente, usa este botón (no toca stock, ni ningún
                    otro dato, solo el precio calculado de cada presentación):
                </p>

        <button type="submit" class="btn btn-gold">Guardar cambios</button>
    </form>

    <form action="{{ route('admin.configuracion.recalcular_precios') }}" method="POST"
          onsubmit="return confirm('Esto recalculará el precio de las presentaciones (100/50/30 ml) de TODAS las fragancias del catálogo con el recargo actualmente guardado. ¿Continuar?');"
          class="mt-2">
        @csrf
        <button type="submit" class="btn btn-outline-secondary">
            Recalcular precios de todo el catálogo ahora
        </button>
    </form>
</div>
@endsection
