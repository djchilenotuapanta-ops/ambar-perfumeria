@extends('layouts.template-back')
@section('title', 'Pedidos')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Pedidos</h3>
            <p class="text-muted mb-0">Gestiona todos los pedidos de Ambar</p>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-mobile-stack">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $p)
                        <tr>
                            <td data-label="Número"><strong>{{ $p->numero_pedido }}</strong></td>
                            <td data-label="Cliente">
                                <div>{{ $p->user->name }}</div>
                                <div class="text-muted small">{{ $p->user->email }}</div>
                            </td>
                            <td data-label="Fecha">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                            <td data-label="Total"><strong>${{ number_format($p->total, 0, ',', '.') }}</strong> USD</td>
                            <td data-label="Pago">
                                @if($p->pago_estado === 'pagado')
                                    <span class="badge bg-success">Pagado</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @endif
                                @if($p->pago_metodo === 'transferencia' && $p->comprobante_pago && $p->pago_estado === 'pendiente')
                                    <span class="badge bg-primary" title="Comprobante subido, pendiente de revisar">
                                        <i class="fas fa-file-invoice"></i> Comprobante
                                    </span>
                                @endif
                            </td>
                            <td data-label="Estado"><x-pedido-estado-badge :estado="$p->estado" /></td>
                            <td data-label="Acciones">
                                <a href="{{ route('admin.pedidos.show', $p->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($p->pago_estado === 'pendiente' && ($p->pago_metodo === 'contraentrega' || $p->comprobante_pago))
                                <form id="formPagar{{ $p->id }}" action="{{ route('admin.pedidos.estado', $p->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirmarMarcarPagado(event, {{ $p->id }})">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="{{ $p->estado }}">
                                    <input type="hidden" name="pago_estado" value="pagado">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Marcar como pagado">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @elseif($p->pago_estado === 'pendiente' && $p->pago_metodo === 'transferencia')
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                        title="Esperando que el cliente suba el comprobante">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No hay pedidos registrados aún.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $pedidos->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmarMarcarPagado(event, pedidoId) {
    event.preventDefault();
    confirmarAccion({
        titulo: 'Marcar como pagado',
        mensaje: '¿Confirmas que este pedido ya fue pagado?',
        textoBoton: 'Confirmar',
        variante: 'primary',
        onConfirmar: function () {
            document.getElementById('formPagar' + pedidoId).submit();
        }
    });
    return false;
}
</script>
@endsection
