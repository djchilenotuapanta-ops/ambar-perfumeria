@extends('layouts.template-back')
@section('title', 'Fragancias sin costo')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h3>Fragancias sin costo configurado</h3>
            <p class="text-muted mb-0">
                Estas {{ $fragancias->count() }} fragancia(s) no tienen <code>costo_por_ml</code> real cargado,
                así que los reportes de ganancia las están tratando como costo $0.
            </p>
        </div>
        <a href="{{ route('admin.configuracion.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-sliders-h me-1"></i> Ajustar margen objetivo
        </a>
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

    <div class="admin-card mb-3">
        <div class="admin-card-header"><h6 class="mb-0">Opción 1 · Aplicar costo estimado a todas</h6></div>
        <div class="admin-card-body">
            <p class="small text-muted mb-2">
                Calcula un costo <strong>ficticio</strong> (de arranque) para cada fragancia de esta lista, a partir
                de su precio de venta y el margen objetivo configurado en
                <a href="{{ route('admin.configuracion.index') }}">Admin &gt; Configuración</a>:
                actualmente
                @if($margenObjetivo['modo'] === 'valor_fijo')
                    <strong>${{ number_format($margenObjetivo['valor_fijo'], 2) }} fijos</strong> de ganancia sobre el frasco de 100&nbsp;ml.
                @else
                    <strong>{{ number_format($margenObjetivo['porcentaje'], 0) }}%</strong> de margen de ganancia sobre el precio de venta.
                @endif
                Ese % (o monto fijo) es editable en cualquier momento desde esa misma pantalla.
            </p>
            <p class="small text-danger mb-3">
                <i class="fas fa-triangle-exclamation me-1"></i>
                Es un valor estimado, no el costo real. Los reportes lo marcarán con ⏱ hasta que lo reemplaces.
            </p>
            <form action="{{ route('admin.fragancias.sin-costo.aplicar-estimado') }}" method="POST"
                  onsubmit="return confirm('¿Aplicar el costo estimado a las {{ $fragancias->count() }} fragancias de esta lista?');">
                @csrf
                <button type="submit" class="btn btn-gold btn-sm" @disabled($fragancias->isEmpty())>
                    <i class="fas fa-wand-magic-sparkles me-1"></i> Aplicar costo estimado a todas
                </button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h6 class="mb-0">Opción 2 · Cargar el costo real, una por una</h6></div>
        <div class="admin-card-body p-0">
            @if($fragancias->isEmpty())
                <p class="text-muted text-center py-4 mb-0">🎉 Todas las fragancias tienen costo configurado.</p>
            @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fragancia</th>
                            <th>Género</th>
                            <th>Precio/ml</th>
                            <th>Costo estimado (con margen actual)</th>
                            <th>Costo real</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fragancias as $f)
                        <tr>
                            <td>
                                <strong>{{ $f->nombre }}</strong>
                                <div class="text-muted small">{{ $f->casa_perfumista }}</div>
                            </td>
                            <td class="text-capitalize">{{ $f->genero }}</td>
                            <td>${{ number_format($f->precio_por_ml, 4) }}</td>
                            <td>
                                @if($f->costo_estimado > 0)
                                    <span class="text-muted">${{ number_format($f->costo_estimado, 4) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="min-width:140px">
                                <form action="{{ route('admin.fragancias.costo-manual', $f->id) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="costo_por_ml" step="0.0001" min="0" max="20"
                                           class="form-control form-control-sm" placeholder="0.0000"
                                           value="{{ $f->costo_estimado > 0 ? $f->costo_estimado : '' }}" required>
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Guardar costo real">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.fragancias.edit', $f->id) }}" class="action-btn action-btn-edit" title="Editar completo">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
