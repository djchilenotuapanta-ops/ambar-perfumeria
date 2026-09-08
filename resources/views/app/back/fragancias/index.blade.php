@extends('layouts.template-back')
@section('title', 'Fragancias')

@section('styles')
<style>
    .admin-content {
        padding: 1rem 1.25rem 1.5rem;
    }

    .catalogo-list-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 0 .25rem;
    }

    .catalogo-list-toolbar h3 {
        margin: 0;
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        color: var(--ep-primary);
    }

    .catalogo-list-tools {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .catalogo-table-wrapper {
        background: #f4f2f0;
        border: 1px solid #e9e1db;
        border-radius: 10px;
        overflow: hidden;
    }

    .catalogo-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        margin: 0;
    }

    .catalogo-table thead th {
        background: #eeeeee;
        color: #7d655d;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-size: .72rem;
        font-weight: 700;
        padding: .9rem 1rem;
        border-bottom: 1px solid #e8e1dc;
        text-align: left;
    }

    .catalogo-table tbody td {
        padding: .95rem 1rem;
        border-bottom: 1px solid #f1ecea;
        vertical-align: middle;
    }

    .catalogo-table tbody tr:hover {
        background: #faf7f4;
    }

    .catalogo-product {
        display: flex;
        align-items: center;
        gap: .8rem;
        min-width: 220px;
    }

    .catalogo-product img {
        width: 42px;
        height: 42px;
        object-fit: cover;
        border-radius: 9px;
        background: var(--ep-ivory);
        border: 1px solid var(--ep-border);
    }

    .catalogo-product-name {
        font-weight: 700;
        color: var(--ep-primary);
        line-height: 1.2;
    }

    .catalogo-product-brand {
        font-size: .74rem;
        color: var(--ep-muted);
    }

    .catalogo-family-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .32rem .7rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 600;
        background: rgba(126, 90, 55, 0.1);
        color: #6b5247;
        white-space: nowrap;
    }

    .catalogo-genre {
        font-size: .8rem;
        color: var(--ep-primary);
        text-transform: capitalize;
    }

    .catalogo-price {
        font-weight: 700;
        color: var(--ep-primary);
        white-space: nowrap;
    }

    .catalogo-stock {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        max-width: 260px;
    }

    .stock-chip {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: .35rem;
        min-width: 92px;
        padding: .2rem .35rem .2rem .55rem;
        border-radius: 8px;
        font-size: .72rem;
        font-weight: 700;
        line-height: 1.2;
        background: #edf7ee;
        color: #2f7a3b;
        border: 1px solid rgba(47,122,59,.12);
    }

    .stock-chip-low {
        background: #fff3df;
        color: #9c6f1e;
        border-color: rgba(156,111,30,.16);
    }

    .stock-chip-out {
        background: #fbe9e9;
        color: #b33636;
        border-color: rgba(179,54,54,.14);
    }

    .stock-chip .stock-ml {
        opacity: .8;
        font-weight: 600;
        margin-right: .1rem;
    }

    .stock-chip-add {
        width: 18px;
        height: 18px;
        border: none;
        border-radius: 50%;
        background: rgba(0,0,0,.08);
        color: inherit;
        font-size: .6rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: .8;
        transition: all .15s ease;
    }

    .stock-chip-add:hover {
        opacity: 1;
        background: rgba(0,0,0,.15);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .28rem .7rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }

    .status-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-pill-activo {
        background: rgba(46,125,50,.1);
        color: #2e7d32;
    }

    .status-pill-inactivo {
        background: rgba(133, 108, 96, .12);
        color: var(--ep-muted);
    }

    .catalogo-actions {
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .action-btn {
        width: 30px;
        height: 30px;
        border: 1px solid #d9d1cc;
        border-radius: 8px;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #7d655d;
        text-decoration: none;
        transition: all .15s ease;
        cursor: pointer;
    }

    .action-btn:hover {
        border-color: currentColor;
        transform: translateY(-1px);
        background: #f9f4ef;
    }

    .action-btn-view:hover { color: #2e2d30; }
    .action-btn-edit:hover { color: #7f6636; }
    .action-btn-delete:hover { color: #b03a3a; }

    .catalogo-filtros {
        background: #f4f2f0;
        border: 1px solid #e9e1db;
        border-radius: 10px;
        padding: .9rem 1rem;
        margin-bottom: 1rem;
    }

    .catalogo-filtros .form-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #7d655d;
        font-weight: 700;
        margin-bottom: .3rem;
    }

    .catalogo-filtros-resumen {
        font-size: .8rem;
        color: var(--ep-muted);
        margin-top: .6rem;
    }

    @media (max-width: 767px) {
        .catalogo-list-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }

        /* El apilado base (ocultar thead, celdas con etiqueta, etc.) vive en
           .table-mobile-stack (public/assets/admin/css/style.css), compartido
           con familias/pedidos/usuarios. Aquí solo la excepción de este listado:
           la celda "Fragancia" ya se identifica sola (imagen+nombre). */
        .catalogo-table tbody td[data-label="Fragancia"]::before {
            display: none;
        }
    }
</style>
@endsection

@section('contenido')
<div class="admin-content">
    <div class="catalogo-list-toolbar">
        <h3>Fragancias</h3>
        <div class="catalogo-list-tools">
            @php $sinCosto = \App\Models\Fragancia::whereNull('costo_por_ml')->count(); @endphp
            @if($sinCosto > 0)
            <a href="{{ route('admin.fragancias.sin-costo') }}" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-triangle-exclamation me-1"></i> {{ $sinCosto }} sin costo
            </a>
            @endif
            <a href="{{ route('admin.fragancias.importar.form') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-file-excel me-1"></i> Carga masiva
            </a>
            <a href="{{ route('admin.fragancias.create') }}" class="btn btn-gold btn-sm">
                <i class="fas fa-plus me-1"></i> Nueva Fragancia
            </a>
        </div>
    </div>

    <div class="catalogo-filtros">
        <form method="GET" action="{{ route('admin.fragancias.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="filtroBuscar">Buscar</label>
                    <input type="text" id="filtroBuscar" name="buscar" class="form-control form-control-sm"
                           placeholder="Nombre o casa perfumista..." value="{{ request('buscar') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="filtroFamilia">Familia</label>
                    <select id="filtroFamilia" name="familia_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($familias as $fam)
                            <option value="{{ $fam->id }}" {{ (string) request('familia_id') === (string) $fam->id ? 'selected' : '' }}>
                                {{ $fam->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="filtroGenero">Género</label>
                    <select id="filtroGenero" name="genero" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="mujer" {{ request('genero') === 'mujer' ? 'selected' : '' }}>Mujer</option>
                        <option value="hombre" {{ request('genero') === 'hombre' ? 'selected' : '' }}>Hombre</option>
                        <option value="unisex" {{ request('genero') === 'unisex' ? 'selected' : '' }}>Unisex</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="filtroEstado">Estado</label>
                    <select id="filtroEstado" name="estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check mb-1">
                        <input type="checkbox" class="form-check-input" id="filtroUrgente" name="urgente" value="1"
                               {{ request('urgente') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="filtroUrgente">Solo stock urgente</label>
                    </div>
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-gold btn-sm flex-grow-1" title="Aplicar filtros">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['buscar','familia_id','genero','estado','urgente']))
            <div class="catalogo-filtros-resumen">
                {{ $fragancias->total() }} resultado(s) con estos filtros.
                <a href="{{ route('admin.fragancias.index') }}">Limpiar filtros</a>
            </div>
            @endif
        </form>
    </div>

    <div class="catalogo-table-wrapper">
        <table class="catalogo-table table-mobile-stack">
            <thead>
                <tr>
                    <th>Fragancia</th>
                    <th>Familia</th>
                    <th>Género</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fragancias as $f)
                    <tr>
                        <td data-label="Fragancia">
                            <div class="catalogo-product">
                                <img src="{{ $f->imagenMostrar() }}" alt="{{ $f->nombre }}">
                                <div>
                                    <div class="catalogo-product-name">{{ $f->nombre }}</div>
                                    <div class="catalogo-product-brand">{{ $f->casa_perfumista }}</div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Familia">
                            <span class="catalogo-family-pill" style="background:{{ $f->familia->color() }}18;color:{{ $f->familia->color() }};">
                                {{ $f->familia->nombre }}
                            </span>
                        </td>
                        <td class="catalogo-genre" data-label="Género">{{ ucfirst($f->genero) }}</td>
                        <td class="catalogo-price" data-label="Precio">${{ number_format($f->precioFinal(), 0, ',', '.') }}</td>
                        <td data-label="Stock">
                            <div class="catalogo-stock">
                                @forelse($f->tamanos as $t)
                                    @php
                                        $estadoStock = $t->stock <= 0 ? 'out' : ($t->stock <= ($umbral ?? 5) ? 'low' : 'ok');
                                    @endphp
                                    <span class="stock-chip {{ $estadoStock === 'out' ? 'stock-chip-out' : ($estadoStock === 'low' ? 'stock-chip-low' : '') }}"
                                          title="{{ $estadoStock === 'out' ? 'Agotado' : ($estadoStock === 'low' ? 'Stock urgente' : 'Stock disponible') }}">
                                        <span><span class="stock-ml">{{ $t->tamano }}</span>: {{ $t->stock }}</span>
                                        <button type="button"
                                                onclick="abrirAgregarStock({{ $t->id }}, '{{ addslashes($f->nombre) }}', '{{ addslashes($t->tamano) }}', {{ $t->stock }})"
                                                class="stock-chip-add" title="Agregar stock a {{ $t->tamano }}"
                                                aria-label="Agregar stock a {{ $f->nombre }} {{ $t->tamano }}">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </span>
                                @empty
                                    <span class="text-muted small">Sin tamaños</span>
                                @endforelse
                            </div>
                        </td>
                        <td data-label="Estado">
                            @if($f->activo)
                                <span class="status-pill status-pill-activo">Activo</span>
                            @else
                                <span class="status-pill status-pill-inactivo">Inactivo</span>
                            @endif
                        </td>
                        <td data-label="Acciones">
                            <div class="catalogo-actions">
                                <a href="{{ route('fragancia.show', $f->slug) }}" target="_blank" class="action-btn action-btn-view"
                                   title="Ver en tienda" aria-label="Ver {{ $f->nombre }} en la tienda">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.fragancias.edit', $f->id) }}" class="action-btn action-btn-edit"
                                   title="Editar" aria-label="Editar {{ $f->nombre }}">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button" onclick="eliminar({{ $f->id }}, '{{ $f->nombre }}')" class="action-btn action-btn-delete"
                                        title="Eliminar" aria-label="Eliminar {{ $f->nombre }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            @if(request()->hasAny(['buscar','familia_id','genero','estado','urgente']))
                                No hay fragancias que coincidan con esos filtros. <a href="{{ route('admin.fragancias.index') }}">Limpiar filtros</a>.
                            @else
                                No hay fragancias. ¡Añade la primera!
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $fragancias->links() }}
</div>

<form id="fEliminar" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

<div class="modal fade" id="modalAgregarStock" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="formAgregarStock">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Agregar stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">Fragancia: <strong id="stockFraganciaNombre"></strong> — <span id="stockFraganciaTamano"></span></p>
                    <p class="text-muted small mb-3">Stock actual de ese tamaño: <span id="stockFraganciaActual"></span> unidades</p>
                    <label class="form-label fw-600">¿Cuántas unidades vas a agregar?</label>
                    <input type="number" name="cantidad" min="1" step="1"
                           class="form-control" placeholder="Ej: 5" required autofocus>
                    <div class="form-text">Esta cantidad se sumará al stock actual de ese tamaño, no lo reemplaza.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-plus-circle me-1"></i> Agregar al stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function eliminar(id, nombre) {
    confirmarAccion({
        titulo: 'Eliminar fragancia',
        mensaje: '¿Eliminar la fragancia "' + nombre + '"? Esta acción no se puede deshacer.',
        textoBoton: 'Eliminar',
        variante: 'danger',
        onConfirmar: function () {
            const f = document.getElementById('fEliminar');
            f.action = '/admin/fragancias/' + id;
            f.submit();
        }
    });
}

function abrirAgregarStock(tamanoId, nombreFragancia, nombreTamano, stockActual) {
    document.getElementById('stockFraganciaNombre').textContent = nombreFragancia;
    document.getElementById('stockFraganciaTamano').textContent = nombreTamano;
    document.getElementById('stockFraganciaActual').textContent = stockActual;
    const form = document.getElementById('formAgregarStock');
    form.action = '/admin/fragancia-tamanos/' + tamanoId + '/agregar-stock';
    const modal = new bootstrap.Modal(document.getElementById('modalAgregarStock'));
    modal.show();
}
</script>
@endsection
