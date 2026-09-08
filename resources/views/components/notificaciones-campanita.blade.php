@props(['prefix' => ''])

@php
    $usuarioActual = auth()->user();
@endphp

@if(! $usuarioActual || ! $usuarioActual->esAdmin())
    {{-- Componente oculto para usuarios que no son administradores --}}
@else
    @php
        $stockTipos = ['stock_bajo', 'stock_agotado'];
        $noLeidas = $usuarioActual->unreadNotifications
                    ->filter(function($n) use ($stockTipos) {
                        return in_array($n->data['tipo'] ?? '', $stockTipos, true);
                    })->count();

        $recientes = $usuarioActual->notifications()->latest()->take(20)->get()
                    ->filter(function($n) use ($stockTipos) {
                        return in_array($n->data['tipo'] ?? '', $stockTipos, true);
                    })->take(6);
    @endphp

    <div class="dropdown notif-campanita">
        <button class="circle-btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
            <i class="fas fa-bell"></i>
            @if($noLeidas > 0)
                <span class="notif-badge">{{ $noLeidas > 9 ? '9+' : $noLeidas }}</span>
            @endif
        </button>
        <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0">
            <div class="notif-dropdown-header">
                <strong>Notificaciones</strong>
                @if($noLeidas > 0)
                    <form action="{{ route($prefix.'notificaciones.marcarTodas') }}" method="POST" class="m-0">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-link btn-sm p-0">Marcar todas leídas</button>
                    </form>
                @endif
            </div>
            <div class="notif-dropdown-body">
                @forelse($recientes as $n)
                    <a href="{{ route($prefix.'notificaciones.ir', $n->id) }}" class="notif-item {{ is_null($n->read_at) ? 'no-leida' : '' }}">
                        <i class="{{ $n->data['icono'] ?? 'fas fa-bell' }} text-{{ $n->data['color'] ?? 'secondary' }}"></i>
                        <div class="notif-item-texto">
                            <div class="notif-titulo">{{ $n->data['titulo'] ?? 'Notificación' }}</div>
                            <div class="notif-mensaje">{{ \Illuminate\Support\Str::limit($n->data['mensaje'] ?? '', 70) }}</div>
                            <div class="notif-fecha">{{ $n->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @empty
                    <div class="notif-vacio">No hay alertas de inventario.</div>
                @endforelse
            </div>
            <div class="notif-dropdown-footer">
                <a href="{{ route($prefix.'notificaciones.index') }}">Ver todas las notificaciones</a>
            </div>
        </div>
    </div>
@endif
