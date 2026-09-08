@extends('layouts.template-back')
@section('title', 'Notificaciones')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Notificaciones</h3>
            <p class="text-muted mb-0">
                @if($noLeidas > 0)
                    Tienes <strong>{{ $noLeidas }}</strong> notificación(es) sin leer.
                @else
                    Estás al día, no tienes notificaciones pendientes.
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($noLeidas > 0)
                <form action="{{ route('admin.notificaciones.marcarTodas') }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-check-double"></i> Marcar todas como leídas
                    </button>
                </form>
            @endif
            <form id="fEliminarLeidas" action="{{ route('admin.notificaciones.eliminarLeidas') }}" method="POST"
                  onsubmit="return confirmarEliminarLeidas(event);">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash"></i> Eliminar leídas
                </button>
            </form>
        </div>
    </div>

    <div class="admin-card mb-3">
        <div class="admin-card-body">
            <form action="{{ route('admin.notificaciones.index') }}" method="GET" class="row g-2 align-items-end">
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
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="{{ route('admin.notificaciones.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-body p-0">
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
                            <a href="{{ route('admin.notificaciones.ir', $n->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                        @endif
                        @if(is_null($n->read_at))
                            <form action="{{ route('admin.notificaciones.leida', $n->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-success w-100">Leída</button>
                            </form>
                        @else
                            <form action="{{ route('admin.notificaciones.noLeida', $n->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">No leída</button>
                            </form>
                        @endif
                        <form id="fEliminarNotif{{ $n->id }}" action="{{ route('admin.notificaciones.eliminar', $n->id) }}" method="POST"
                              onsubmit="return confirmarEliminarNotificacion(event, {{ $n->id }});">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">No hay notificaciones con esos filtros.</div>
            @endforelse
        </div>
        @if($notificaciones->hasPages())
            <div class="admin-card-body">
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
            document.getElementById('fEliminarLeidas').submit();
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
            document.getElementById('fEliminarNotif' + id).submit();
        }
    });
    return false;
}
</script>
@endsection
