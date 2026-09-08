@extends('layouts.template-back')
@section('title', 'Previsualizar carga masiva')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Previsualizar carga masiva</h3>
            <p class="text-muted mb-0">Revisa qué se va a crear o actualizar antes de confirmar</p>
        </div>
        <a href="{{ route('admin.fragancias.importar.form') }}" class="btn btn-outline-secondary">
            ← Subir otro archivo
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="admin-card">
                <div class="admin-card-body text-center">
                    <div class="fs-3 fw-bold text-success">{{ collect($validas)->where('accion', 'crear')->count() }}</div>
                    <div class="text-muted small">Fragancias nuevas a crear</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card">
                <div class="admin-card-body text-center">
                    <div class="fs-3 fw-bold text-primary">{{ collect($validas)->where('accion', 'actualizar')->count() }}</div>
                    <div class="text-muted small">Fragancias existentes a actualizar</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card">
                <div class="admin-card-body text-center">
                    <div class="fs-3 fw-bold {{ count($invalidas) > 0 ? 'text-danger' : 'text-muted' }}">{{ count($invalidas) }}</div>
                    <div class="text-muted small">Filas con errores (se omitirán)</div>
                </div>
            </div>
        </div>
    </div>

    @if(count($invalidas) > 0)
    <div class="admin-card mb-3 border-danger">
        <div class="admin-card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0 text-danger"><i class="fas fa-triangle-exclamation me-1"></i> Filas con errores</h5>
                <a href="{{ route('admin.fragancias.importar.errores', $token) }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-download me-1"></i> Descargar reporte de errores (.csv)
                </a>
            </div>
            <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                <table class="table table-sm mb-0 table-mobile-stack">
                    <thead>
                        <tr><th style="width: 70px;">Fila</th><th>Errores</th></tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($invalidas, 0, 30) as $inv)
                        <tr>
                            <td data-label="Fila">{{ $inv['fila'] }}</td>
                            <td data-label="Errores" class="small text-danger">{{ implode(' · ', $inv['errores']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($invalidas) > 30)
                <div class="small text-muted mt-2">Mostrando 30 de {{ count($invalidas) }}. Descarga el reporte para verlas todas.</div>
            @endif
        </div>
    </div>
    @endif

    @if(count($validas) > 0)
    @php $totalActualizar = collect($validas)->where('accion', 'actualizar')->count(); @endphp

    <div class="alert alert-secondary small">
        <i class="fas fa-clock me-1"></i>
        Esta previsualización expira a las <strong>{{ $expiraEn->format('H:i') }}</strong>
        ({{ $expiraEn->diffForHumans(now(), true) }} desde ahora). Si se pasa el tiempo, tendrás que subir el archivo de nuevo.
    </div>

    @if($totalActualizar > 0)
    <div class="alert alert-warning small">
        <i class="fas fa-triangle-exclamation me-1"></i>
        {{ $totalActualizar }} fragancia(s) existente(s) se van a <strong>actualizar</strong>: su stock
        quedará reemplazado por el valor de este archivo (no se suma al que ya tenían). Revisa la columna
        de stock antes de confirmar.
    </div>
    @endif
    <div class="admin-card mb-3">
        <div class="admin-card-body">
            <h5 class="mb-3"><i class="fas fa-list-check me-1"></i> Filas listas para importar</h5>
            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                <table class="table table-sm mb-0 table-mobile-stack">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Fila</th>
                            <th style="width: 100px;">Acción</th>
                            <th>Nombre</th>
                            <th>Casa</th>
                            <th>Familia</th>
                            <th>Género</th>
                            <th>$/ml</th>
                            <th>Stock (100/50/30)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($validas as $v)
                        <tr>
                            <td data-label="Fila">{{ $v['fila'] }}</td>
                            <td data-label="Acción">
                                @if($v['accion'] === 'crear')
                                    <span class="badge bg-success">Crear</span>
                                @else
                                    <span class="badge bg-primary">Actualizar</span>
                                @endif
                            </td>
                            <td data-label="Nombre">{{ $v['payload']['nombre'] }}</td>
                            <td data-label="Casa">{{ $v['payload']['casa_perfumista'] }}</td>
                            <td data-label="Familia">
                                {{ $v['payload']['familia'] }}
                                @if($v['familia_nueva'])
                                    <span class="badge bg-warning text-dark">nueva</span>
                                @endif
                            </td>
                            <td data-label="Género">{{ ucfirst($v['payload']['genero']) }}</td>
                            <td data-label="$/ml">{{ number_format((float) $v['payload']['precio_por_ml'], 2) }}</td>
                            <td data-label="Stock (100/50/30)">{{ $v['stocks'][100] }} / {{ $v['stocks'][50] }} / {{ $v['stocks'][30] }}</td>
                        </tr>
                        @if($v['accion'] === 'actualizar' && count($v['cambios']) > 0)
                        <tr class="table-light">
                            <td></td>
                            <td colspan="7" class="small">
                                <i class="fas fa-code-compare me-1 text-primary"></i>
                                <span class="text-muted">Cambios respecto a lo que ya existe:</span>
                                @foreach($v['cambios'] as $cambio)
                                    <span class="badge bg-light text-dark border me-1 mb-1">
                                        {{ $cambio['campo'] }}:
                                        <span class="text-danger text-decoration-line-through">{{ $cambio['antes'] }}</span>
                                        →
                                        <span class="text-success fw-semibold">{{ $cambio['despues'] }}</span>
                                    </span>
                                @endforeach
                            </td>
                        </tr>
                        @elseif($v['accion'] === 'actualizar')
                        <tr class="table-light">
                            <td></td>
                            <td colspan="7" class="small text-muted">
                                <i class="fas fa-circle-check me-1"></i> Sin cambios respecto a lo que ya existe (solo se re-guarda igual).
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.fragancias.importar.confirmar') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <button type="submit" class="btn btn-gold">
            <i class="fas fa-check me-1"></i> Confirmar e importar {{ count($validas) }} fragancia(s)
        </button>
        <a href="{{ route('admin.fragancias.importar.form') }}" class="btn btn-outline-secondary">Cancelar</a>
    </form>
    @else
    <div class="alert alert-danger">No hay ninguna fila válida para importar. Corrige el archivo y vuelve a subirlo.</div>
    <a href="{{ route('admin.fragancias.importar.form') }}" class="btn btn-outline-secondary">Volver</a>
    @endif
</div>
@endsection
