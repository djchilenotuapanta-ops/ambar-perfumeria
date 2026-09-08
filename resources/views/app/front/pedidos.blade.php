@extends('layouts.template')
@section('title', 'Mis Pedidos')

@section('contenido')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0">Historial de Pedidos</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Volver a mi cuenta</a>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-body">
            @if($pedidos->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-3 table-mobile-stack">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidos as $pedido)
                        <tr>
                            <td data-label="Pedido"><strong>{{ $pedido->numero_pedido }}</strong></td>
                            <td data-label="Fecha">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                            <td data-label="Total">${{ number_format($pedido->total, 0, ',', '.') }} USD</td>
                            <td data-label="Estado"><x-pedido-estado-badge :estado="$pedido->estado" /></td>
                            <td data-label="Acción" class="text-end">
                                <a href="{{ route('pedidos.confirmacion', $pedido->numero_pedido) }}" class="btn btn-outline-secondary btn-sm">Ver detalle</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $pedidos->links() }}
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-box-open fa-2x mb-2"></i>
                <p class="mb-3">Aún no tienes pedidos registrados.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-dark-perfume btn-sm">Regresar al panel</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
