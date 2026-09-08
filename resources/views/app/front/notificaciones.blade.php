@extends('layouts.template')
@section('title', 'Mis Notificaciones')

@section('contenido')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-0">Mis Notificaciones</h2>
            <p class="text-muted mb-0">
                @if($noLeidas > 0)
                    Tienes <strong>{{ $noLeidas }}</strong> notificación(es) sin leer.
                @else
                    Estás al día.
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($noLeidas > 0)
                <form action="{{ route('notificaciones.marcarTodas') }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Marcar todas como leídas</button>
                </form>
            @endif
            <form id="fEliminarLeidas" action="{{ route('notificaciones.eliminarLeidas') }}" method="POST"
                  onsubmit="return confirmarEliminarLeidas(event)">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar leídas</button>
            </form>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Volver a mi cuenta</a>
        </div>
    </div>

    <div class="dashboard-card mb-3">
        <div class="dashboard-card-body">
            <form action="{{ route('notificaciones.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="no_leidas" {{ request('estado') === 'no_leidas' ? 'selected' : '' }}>No leídas</option>
                        <option value="leidas"    {{ request('estado') === 'leidas'    ? 'selected' : '' }}>Leídas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark-perfume btn-sm">Filtrar</button>
                    <a href="{{ route('notificaciones.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-body p-0">
            @forelse($notificaciones as $n)
                <div class="d-flex align-items-start gap-3 p-3 border-bottom {{ is_null($n->read_at) ? 'bg-light' : '' }}">
                    <i class="{{ $n->data['icono'] ?? 'fas fa-bell' }} text-{{ $n->data['color'] ?? 'secondary' }} mt-1"></i>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $n->data['titulo'] ?? 'Notificación' }}</strong>
                            <span class="text-muted small">{{ $n->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="text-muted small">{{ $n->data['mensaje'] ?? '' }}</div>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        @if(!empty($n->data['url']))
                            <a href="{{ route('notificaciones.ir', $n->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                        @endif
                        @if(is_null($n->read_at))
                            <form action="{{ route('notificaciones.leida', $n->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-success w-100">Leída</button>
                            </form>
                        @else
                            <form action="{{ route('notificaciones.noLeida', $n->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">No leída</button>
                            </form>
                        @endif
                        <form id="fEliminarNotif{{ $n->id }}" action="{{ route('notificaciones.eliminar', $n->id) }}" method="POST"
                              onsubmit="return confirmarEliminarNotificacion(event, {{ $n->id }})">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-bell-slash fa-2x mb-2"></i>
                    <p class="mb-0">No hay notificaciones con esos filtros.</p>
                </div>
            @endforelse
        </div>
        @if($notificaciones->hasPages())
            <div class="dashboard-card-body">
                {{ $notificaciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmarEliminarLeidas(event) {
    event.preventDefault();
    confirmarAccion({
        titulo: 'Eliminar notificaciones leídas',
        mensaje: '¿Eliminar todas las notificaciones ya leídas? Esta acción no se puede deshacer.',
        onConfirmar: function () {
            window.__submitFormSegura(document.getElementById('fEliminarLeidas'));
        }
    });
    return false;
}

function confirmarEliminarNotificacion(event, id) {
    event.preventDefault();
    confirmarAccion({
        titulo: 'Eliminar notificación',
        mensaje: '¿Eliminar esta notificación?',
        onConfirmar: function () {
            window.__submitFormSegura(document.getElementById('fEliminarNotif' + id));
        }
    });
    return false;
}
</script>
@endsection
