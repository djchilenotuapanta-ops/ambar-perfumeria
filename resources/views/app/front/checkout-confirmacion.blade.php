@extends('layouts.template')
@section('title', 'Pedido Confirmado')

@section('contenido')
<div class="container py-5" style="max-width:680px;">

    <div class="text-center mb-5">
        <div style="font-size:3.5rem;">🌸</div>
        <h2 style="font-family:'Cormorant Garamond',serif;color:#2C1810;margin-top:.5rem;">
            ¡Pedido Registrado con Éxito!
        </h2>
        <p style="color:#8B6F5E;">
            Tu pedido <strong>{{ $pedido->numero_pedido }}</strong> ha sido recibido.
            Te contactaremos para confirmar el pago y coordinar el envío.
        </p>
    </div>

    <div class="admin-card mb-4">
        <div class="admin-card-header">
            <h5 class="mb-0">Detalle del Pedido</h5>
            <x-pedido-estado-badge :estado="$pedido->estado" />
        </div>
        <div class="admin-card-body">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fragancia</th>
                        <th>Tamaño</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">Precio unit.</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->detalles as $d)
                    @php($cantidad = max(1, (int) $d->cantidad))
                    @php($precioUnit = $d->precio_unitario ?? ($cantidad ? ($d->subtotal / $cantidad) : 0))
                    <tr>
                        <td>{{ $d->nombre_fragancia }}</td>
                        <td>{{ $d->tamano ?? '—' }}</td>
                        <td class="text-center">{{ $d->cantidad }}</td>
                        <td class="text-end">${{ number_format($precioUnit, 0, ',', '.') }}</td>
                        <td class="text-end">${{ number_format($d->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if(!empty($d->regalo_presentacion))
                    @php($nombresRegaloDet = ['blanco' => 'Bolsa de regalo blanca', 'negro' => 'Bolsa de regalo negra'])
                    <tr>
                        <td colspan="4" class="text-end small text-success">Envoltorio {{ $nombresRegaloDet[$d->regalo_presentacion] ?? ucfirst($d->regalo_presentacion) }}</td>
                        <td class="text-end text-success">Sin costo</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot>
                    @php($ivaPedido = \App\Models\Configuracion::desglosarIva((float) $pedido->subtotal))
                    <tr><td colspan="4" class="text-end">Subtotal sin IVA</td><td>${{ number_format($ivaPedido['base'], 2, ',', '.') }}</td></tr>
                    <tr><td colspan="4" class="text-end text-muted small">IVA ({{ number_format($ivaPedido['porcentaje'], 2, ',', '.') }}%)</td><td>${{ number_format($ivaPedido['iva'], 2, ',', '.') }}</td></tr>
                    <tr><td colspan="4" class="text-end">Precio con IVA</td><td>${{ number_format($ivaPedido['conIva'], 2, ',', '.') }}</td></tr>
                    @if($pedido->descuento > 0)
                    <tr><td colspan="4" class="text-end text-success">Descuento</td><td class="text-success">-${{ number_format($pedido->descuento,2,',','.') }}</td></tr>
                    @endif
                    <tr><td colspan="4" class="text-end">Envío</td><td>{{ $pedido->envio == 0 ? 'Gratis' : '$'.number_format($pedido->envio,2,',','.') }}</td></tr>
                    @if($pedido->envio > 0)
                    <tr>
                        <td colspan="5" class="text-end small text-muted" style="border-top:none;">
                            Se cobró envío porque el subtotal (${{ number_format($pedido->subtotal - $pedido->descuento,2,',','.') }})
                            fue menor a ${{ number_format((float) \App\Models\Configuracion::obtener('envio_gratis_desde', config('comercial.envio_gratis_desde', 80)),2,',','.') }},
                            el mínimo para envío gratis.
                        </td>
                    </tr>
                    @endif
                    <tr><td colspan="4" class="text-end fw-700">Total</td><td class="fw-700">${{ number_format($pedido->total,2,',','.') }} USD</td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="resumen-panel p-4 rounded-3 mb-4">
        <h6 class="mb-2">Datos de envío</h6>
        <p class="mb-1 small">{{ $pedido->direccion_envio }}, {{ $pedido->ciudad_envio }}</p>
        @if($pedido->telefono_entrega)
        <p class="mb-1 small">Teléfono de contacto: {{ $pedido->telefono_entrega }}</p>
        @endif
        <p class="mb-0 small text-muted">Método de pago: {{ ucfirst($pedido->pago_metodo) }}</p>
    </div>

    <div class="admin-card mb-4">
        <div class="admin-card-header">
            <h5 class="mb-0">Estado del Pago</h5>
        </div>
        <div class="admin-card-body">
            @if($pedido->pago_estado === 'pendiente')
                @if($pedido->pago_metodo === 'transferencia')
                    @php($cuenta = [
                        'banco' => \App\Models\Configuracion::obtener('banco_nombre', config('comercial.cuenta_bancaria.banco')),
                        'tipo' => \App\Models\Configuracion::obtener('banco_tipo', config('comercial.cuenta_bancaria.tipo')),
                        'numero' => \App\Models\Configuracion::obtener('banco_numero', config('comercial.cuenta_bancaria.numero')),
                        'titular' => \App\Models\Configuracion::obtener('banco_titular', config('comercial.cuenta_bancaria.titular')),
                        'ruc' => \App\Models\Configuracion::obtener('banco_ruc', config('comercial.cuenta_bancaria.ruc')),
                    ])
                    <p class="mb-2 small">
                        Realiza la transferencia por el total del pedido a la siguiente cuenta
                        y sube el comprobante desde esta página. Tu pago se confirmará
                        una vez que el equipo lo verifique.
                    </p>
                    <div class="resumen-panel p-3 rounded-3 mb-3 small">
                        <p class="mb-1"><strong>Banco:</strong> {{ $cuenta['banco'] }}</p>
                        <p class="mb-1"><strong>Tipo de cuenta:</strong> {{ $cuenta['tipo'] }}</p>
                        <p class="mb-1"><strong>Número de cuenta:</strong> {{ $cuenta['numero'] }}</p>
                        <p class="mb-1"><strong>Titular:</strong> {{ $cuenta['titular'] }}</p>
                        <p class="mb-1"><strong>RUC:</strong> {{ $cuenta['ruc'] }}</p>
                    </div>

                    @if($pedido->comprobante_pago)
                        <div class="alert alert-success small mb-3">
                            ✔ Comprobante recibido el {{ $pedido->comprobante_subido_at->format('d/m/Y H:i') }}.
                            Está pendiente de revisión por el equipo.
                        </div>

                        @php($extension = strtolower(pathinfo($pedido->comprobante_pago, PATHINFO_EXTENSION)))
                        @php($esImagen = in_array($extension, ['jpg', 'jpeg', 'png']))
                        @php($urlComprobante = route('pedidos.ver-comprobante', $pedido->numero_pedido))
                        <a href="{{ $urlComprobante }}" target="_blank" class="d-inline-block mb-3">
                            @if($esImagen)
                                <img src="{{ $urlComprobante }}" alt="Comprobante de transferencia"
                                     style="max-width:160px;max-height:160px;object-fit:cover;border:1px solid #E8DDD5;border-radius:8px;">
                            @else
                                <div class="d-flex align-items-center gap-2 p-3" style="border:1px solid #E8DDD5;border-radius:8px;width:160px;">
                                    <i class="fas fa-file-pdf text-danger" style="font-size:1.8rem;"></i>
                                    <span class="small">Ver PDF</span>
                                </div>
                            @endif
                        </a>
                        <form action="{{ route('pedidos.comprobante', $pedido->numero_pedido) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                            @csrf
                            <label class="form-label small text-muted">¿Te equivocaste de archivo? Sube otro:</label>
                            <div class="input-group input-group-sm">
                                <input type="file" name="comprobante" class="form-control @error('comprobante') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                                <button type="submit" class="btn btn-outline-secondary">Reemplazar</button>
                            </div>
                            @error('comprobante')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </form>
                    @else
                        <form action="{{ route('pedidos.comprobante', $pedido->numero_pedido) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                            @csrf
                            <label class="form-label small">Subir comprobante de transferencia (JPG, PNG o PDF, máx. 5MB)</label>
                            <div class="input-group">
                                <input type="file" name="comprobante" class="form-control @error('comprobante') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                                <button type="submit" class="btn btn-gold">Subir comprobante</button>
                            </div>
                            @error('comprobante')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </form>
                    @endif
                @elseif($pedido->pago_metodo === 'contraentrega')
                    <p class="mb-3 small">
                        Pagarás en efectivo al momento de recibir tu pedido. El pago quedará
                        confirmado cuando el repartidor entregue el pedido.
                    </p>
                @endif

                @if($pedido->estado !== 'cancelado')
                <form id="fCancelarPedido" action="{{ route('pedidos.cancelar', $pedido->numero_pedido) }}" method="POST" class="d-inline"
                      onsubmit="return confirmarCancelarPedido(event)">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Cancelar pedido</button>
                </form>
                @endif
            @elseif($pedido->pago_estado === 'pagado')
                <p class="mb-0 text-success fw-700">✔ Pago confirmado</p>
            @else
                <p class="mb-0 text-muted">Estado del pago: {{ ucfirst($pedido->pago_estado) }}</p>
            @endif

            @if($pedido->estado === 'cancelado')
                <p class="mb-0 mt-2 text-danger fw-700">✖ Este pedido fue cancelado.</p>
            @endif
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a href="{{ route('catalogo') }}" class="btn btn-dark-perfume">Explorar fragancias</a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Regresar al panel</a>
    </div>

</div>
@endsection

@section('scripts')
<script>
function confirmarCancelarPedido(event) {
    event.preventDefault();
    confirmarAccion({
        titulo: 'Cancelar pedido',
        mensaje: '¿Seguro que quieres cancelar este pedido? Esta acción no se puede deshacer.',
        textoBoton: 'Cancelar pedido',
        variante: 'danger',
        onConfirmar: function () {
            window.__submitFormSegura(document.getElementById('fCancelarPedido'));
        }
    });
    return false;
}
</script>
@endsection
