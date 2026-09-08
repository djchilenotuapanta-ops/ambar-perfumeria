@extends('layouts.template-back')
@section('title', 'Reporte de Productos')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Reporte de Productos</h3>
            <p class="text-muted mb-0">{{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.reportes.ventas') }}" class="btn btn-outline-secondary btn-sm">Ver Ventas</a>
            @endif
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.reportes.clientes') }}" class="btn btn-outline-secondary btn-sm">Ver Clientes</a>
            @endif
        </div>
    </div>

    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form action="{{ route('admin.reportes.productos') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-1">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm" value="{{ $desde->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $hasta->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-gold btn-sm">Filtrar</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.reportes.productos.pdf', ['desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}"
                       class="btn btn-outline-dark btn-sm" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-6">
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h5>🏆 Fragancias Más Vendidas</h5></div>
                <div class="admin-card-body">
                    @if($masVendidas->count())
                    <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                        <span>Ganancia total del top 10, sin IVA: <strong class="text-success">${{ number_format($masVendidas->sum('ganancia'), 0, ',', '.') }}</strong></span>
                    </div>
                    <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Fragancia</th><th>Unidades</th><th>Ingresos</th><th>Costo</th><th>Ganancia</th><th>Margen</th></tr></thead>
                        <tbody>
                            @foreach($masVendidas as $p)
                            <tr>
                                <td>
                                    {{ $p->nombre_fragancia }}
                                    @if($p->costo_sin_datos)
                                    <i class="fas fa-triangle-exclamation text-warning ms-1" title="Sin costo de elaboración configurado — ganancia calculada de menos"></i>
                                    @elseif($p->costo_estimado)
                                    <i class="fas fa-clock-rotate-left text-muted ms-1" title="Venta anterior al histórico de costos — se usó el costo actual del producto como estimado"></i>
                                    @endif
                                </td>
                                <td><span class="badge bg-success">{{ $p->unidades_vendidas }}</span></td>
                                <td>${{ number_format($p->ingresos, 0, ',', '.') }}</td>
                                <td class="text-muted">${{ number_format($p->costo, 0, ',', '.') }}</td>
                                <td class="{{ $p->ganancia < 0 ? 'text-danger' : 'text-success' }} fw-600">${{ number_format($p->ganancia, 0, ',', '.') }}</td>
                                <td class="{{ $p->ganancia < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($p->margen_pct, 1, ',', '.') }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    @if($resumenGanancia['lineasSinCosto'] > 0)
                    <p class="small text-warning mt-2 mb-0">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        Algunas fragancias no tienen costo de elaboración configurado — su ganancia mostrada
                        está de menos. Complétalo en la ficha de cada producto.
                    </p>
                    @endif
                    @else
                    <p class="text-muted text-center py-3 mb-0">Sin ventas registradas en este período.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h5>⚠️ Stock Crítico ({{ $umbral }} unidades o menos)</h5>
                </div>
                <div class="admin-card-body">
                    @if($stockCritico->count())
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Fragancia</th><th>Stock</th><th></th></tr></thead>
                        <tbody>
                            @foreach($stockCritico as $f)
                            <tr>
                                <td>{{ $f->fragancia->nombre }} <span class="text-muted small">({{ $f->tamano }})</span></td>
                                <td>
                                    @if($f->stock == 0)
                                        <span class="badge bg-dark">Agotado</span>
                                    @else
                                        <span class="badge bg-danger">{{ $f->stock }} unidades</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.fragancias.edit', $f->fragancia_id) }}"
                                       class="btn btn-xs btn-outline-primary">Reponer</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-success text-center py-3 mb-0">✅ Ninguna fragancia con stock crítico.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h5>Sin Ventas en el Período</h5>
        </div>
        <div class="admin-card-body">
            @if($sinMovimiento->count())
            <p class="text-muted small mb-3">
                Estas fragancias activas no tuvieron ninguna venta en el rango seleccionado.
                Pueden ser candidatas a promoción o revisión de precio.
            </p>
            <div class="d-flex flex-wrap gap-2">
                @foreach($sinMovimiento as $f)
                <span class="badge bg-light text-dark border">{{ $f->nombre }}</span>
                @endforeach
            </div>
            @else
            <p class="text-success text-center py-3 mb-0">✅ Todas las fragancias activas tuvieron ventas.</p>
            @endif
        </div>
    </div>
</div>
@endsection
