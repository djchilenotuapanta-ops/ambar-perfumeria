@extends('layouts.template-back')
@section('title', 'Pedido ' . $pedido->numero_pedido)

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Pedido: {{ $pedido->numero_pedido }}</h3>
            <p class="text-muted mb-0">{{ $pedido->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary">← Regresar al panel</a>
    </div>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h5>Fragancias Pedidas</h5></div>
                <div class="admin-card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-mobile-stack">
                            <thead>
                                <tr>
                                    <th>Fragancia</th>
                                    <th>Tamaño</th>
                                    <th>Precio unit.</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedido->detalles as $d)
                                <tr>
                                    <td data-label="Fragancia">
                                        <div class="fw-600">{{ $d->nombre_fragancia }}</div>
                                    </td>
                                    <td data-label="Tamaño">{{ $d->tamano ?? '—' }}</td>
                                    <td data-label="Precio unit.">${{ number_format($d->precio_unitario, 0, ',', '.') }}</td>
                                    <td data-label="Cantidad">{{ $d->cantidad }}</td>
                                    <td data-label="Subtotal"><strong>${{ number_format($d->subtotal, 0, ',', '.') }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-600">Base imponible:</td>
                                    <td>${{ number_format($iva['base'], 0, ',', '.') }}</td>
                                </tr>
                                @if($iva['porcentaje'] > 0)
                                <tr>
                                    <td colspan="4" class="text-end text-primary">IVA ({{ $iva['porcentaje'] }}%):</td>
                                    <td class="text-primary">${{ number_format($iva['iva'], 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-600">Subtotal (con IVA):</td>
                                    <td>${{ number_format($pedido->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td colspan="4" class="text-end fw-600">Subtotal:</td>
                                    <td>${{ number_format($pedido->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if($pedido->descuento > 0)
                                <tr>
                                    <td colspan="4" class="text-end text-success">Descuento:</td>
                                    <td class="text-success">-${{ number_format($pedido->descuento, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="text-end">Envío:</td>
                                    <td>{{ $pedido->envio > 0 ? '$' . number_format($pedido->envio, 0, ',', '.') : 'Gratis' }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-700 fs-6">TOTAL:</td>
                                    <td class="fw-700 fs-6">${{ number_format($pedido->total, 0, ',', '.') }} USD</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">

            <div class="admin-card mb-3">
                <div class="admin-card-header"><h6 class="mb-0">Cliente</h6></div>
                <div class="admin-card-body">
                    <p class="mb-1 fw-600">{{ $pedido->user->name }}</p>
                    <p class="mb-1 text-muted small">{{ $pedido->user->email }}</p>
                    @if($pedido->direccion_envio)
                    <p class="mb-1 small"><i class="fas fa-map-marker-alt me-1"></i>
                        {{ $pedido->direccion_envio }}@if($pedido->ciudad_envio), {{ $pedido->ciudad_envio }}@endif
                    </p>
                    @endif
                    @if($pedido->telefono_entrega)
                    <p class="mb-1 small"><i class="fas fa-phone me-1"></i>
                        {{ $pedido->telefono_entrega }}
                    </p>
                    @endif
                    @if($pedido->notas)
                    <p class="mb-0 small text-muted"><em>"{{ $pedido->notas }}"</em></p>
                    @endif
                </div>
            </div>

            <div class="admin-card mb-3">
                <div class="admin-card-header"><h6 class="mb-0">Pago</h6></div>
                <div class="admin-card-body">
                    <p class="mb-1 small">
                        <strong>Método:</strong>
                        {{ ucfirst($pedido->pago_metodo) }}
                    </p>
                    <p class="mb-0 small">
                        <strong>Estado del pago:</strong> {{ ucfirst($pedido->pago_estado) }}
                    </p>

                    @if($pedido->pago_metodo === 'transferencia')
                        <hr>
                        @if($pedido->comprobante_pago)
                            @php
                                $comprobanteUrl = route('pedidos.ver-comprobante', $pedido->numero_pedido);
                                $comprobanteExtension = strtolower(pathinfo($pedido->comprobante_pago, PATHINFO_EXTENSION));
                            @endphp
                            <p class="mb-1 small text-muted">
                                Comprobante subido el {{ $pedido->comprobante_subido_at?->format('d/m/Y H:i') }}:
                            </p>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100"
                                    data-bs-toggle="modal" data-bs-target="#modalComprobante">
                                <i class="fas fa-file-invoice me-1"></i> Ver comprobante
                            </button>
                        @else
                            <p class="mb-0 small text-muted">El cliente aún no ha subido el comprobante.</p>
                        @endif
                    @endif
                </div>
            </div>

            @if(auth()->user()->role === 'admin' && $pedido->estado === 'pendiente')
            <div class="admin-card">
                <div class="admin-card-header"><h6 class="mb-0">Actualizar Estado</h6></div>
                <div class="admin-card-body">
                    <form action="{{ route('admin.pedidos.estado', $pedido->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Cambiar estado del pedido</label>
                            <select name="estado" class="form-select" required>
                                <option value="">-- Selecciona un estado --</option>
                                <option value="pendiente" selected>Pendiente</option>
                                <option value="entregado">Entregado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado del pago</label>
                            <select name="pago_estado" class="form-select">
                                <option value="">-- Sin cambios --</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagado">Pagado</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-gold w-100">Actualizar Estado</button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif(auth()->user()->role === 'admin')
            <div class="admin-card">
                <div class="admin-card-header"><h6 class="mb-0">Estado del Pedido</h6></div>
                <div class="admin-card-body">
                    <p class="mb-0 text-muted">Este pedido ya fue procesado y no se puede cambiar su estado.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@if($pedido->pago_metodo === 'transferencia' && $pedido->comprobante_pago)
<div class="modal fade" id="modalComprobante" tabindex="-1" aria-labelledby="modalComprobanteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalComprobanteLabel">Comprobante de transferencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="mb-1"><strong>Pedido:</strong> {{ $pedido->numero_pedido }}</p>
                        <p class="mb-0 small text-muted">Subido el {{ $pedido->comprobante_subido_at?->format('d/m/Y H:i') }}</p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-1"></i> Regresar
                    </button>
                </div>
                @if(in_array($comprobanteExtension, ['jpg','jpeg','png']))
                    <img src="{{ $comprobanteUrl }}" alt="Comprobante" class="img-fluid rounded">
                @elseif($comprobanteExtension === 'pdf')
                    <embed src="{{ $comprobanteUrl }}" type="application/pdf" width="100%" height="650px" />
                @else
                    <p class="text-muted">No se puede mostrar el comprobante en línea. <a href="{{ $comprobanteUrl }}" target="_blank">Descargar comprobante</a>.</p>
                @endif
            </div>
            <div class="modal-footer">
                <a href="{{ $comprobanteUrl }}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download me-1"></i> Descargar comprobante
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif
