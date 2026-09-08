@extends('layouts.template')
@section('title', 'Mi Cuenta')

@section('contenido')
<div class="container py-5">
    <div class="row g-4">

        <div class="col-lg-8">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h5 class="mb-0">Mis Últimos Pedidos</h5>
                    <a href="{{ route('pedidos.historial') }}" class="btn btn-sm btn-outline-secondary">Ver todos</a>
                </div>
                <div class="dashboard-card-body">
                    @if($pedidos->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-mobile-stack">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedidos as $p)
                                <tr>
                                    <td data-label="Pedido"><strong>{{ $p->numero_pedido }}</strong></td>
                                    <td data-label="Fecha">{{ $p->created_at->format('d/m/Y') }}</td>
                                    <td data-label="Total">${{ number_format($p->total, 0, ',', '.') }} USD</td>
                                    <td data-label="Estado"><x-pedido-estado-badge :estado="$p->estado" /></td>
                                    <td data-label="Acciones">
                                        <a href="{{ route('pedidos.confirmacion', $p->numero_pedido) }}"
                                           class="btn btn-sm btn-outline-secondary">Ver</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-box-open fa-2x mb-2"></i>
                        <p>Aún no tienes pedidos.</p>
                        <a href="{{ route('catalogo') }}" class="btn btn-dark-perfume btn-sm">Explorar catálogo</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card">
                <div class="dashboard-card-header"><h5 class="mb-0">Accesos Rápidos</h5></div>
                <div class="dashboard-card-body p-0">
                    <a href="{{ route('catalogo') }}" class="quick-link">
                        <i class="fas fa-spray-can"></i> Ver Catálogo
                    </a>
                    <a href="{{ route('carrito.index') }}" class="quick-link">
                        <i class="fas fa-shopping-bag"></i> Mi Carrito
                    </a>
                    <a href="{{ route('notificaciones.index') }}" class="quick-link">
                        <i class="fas fa-bell"></i> Notificaciones
                        @php $noLeidasCliente = auth()->user()->unreadNotifications()->count() @endphp
                        @if($noLeidasCliente > 0)
                            <span class="badge bg-danger ms-1">{{ $noLeidasCliente > 9 ? '9+' : $noLeidasCliente }}</span>
                        @endif
                    </a>
                    <a href="{{ route('profile.edit') }}" class="quick-link">
                        <i class="fas fa-user-edit"></i> Editar Perfil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
