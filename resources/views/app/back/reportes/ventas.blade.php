@extends('layouts.template-back')
@section('title', 'Reporte de Ventas')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Reporte de Ventas</h3>
            <p class="text-muted mb-0">{{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reportes.productos') }}" class="btn btn-outline-secondary btn-sm">Ver Productos</a>
            <a href="{{ route('admin.reportes.clientes') }}" class="btn btn-outline-secondary btn-sm">Ver Clientes</a>
        </div>
    </div>

    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form action="{{ route('admin.reportes.ventas') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-1">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm"
                           value="{{ $desde->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm"
                           value="{{ $hasta->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-gold btn-sm">Filtrar</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.reportes.ventas') }}" class="btn btn-outline-secondary btn-sm">Últimos 30 días</a>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.reportes.ventas.pdf', ['desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}"
                       class="btn btn-outline-dark btn-sm" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#8A6D1F20;color:#8A6D1F;"><i class="fas fa-coins"></i></div>
                <div class="kpi-valor">${{ number_format($totalVentas, 0, ',', '.') }}</div>
                <div class="kpi-label">Ventas confirmadas (pagadas)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#19875420;color:#198754;"><i class="fas fa-hand-holding-dollar"></i></div>
                <div class="kpi-valor">${{ number_format($ganancia['ganancia'], 0, ',', '.') }}</div>
                <div class="kpi-label">Ganancia bruta, sin IVA ({{ $ganancia['margenPorc'] }}% margen)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#C4847A20;color:#C4847A;"><i class="fas fa-receipt"></i></div>
                <div class="kpi-valor">{{ $totalPedidos }}</div>
                <div class="kpi-label">Pedidos en el período</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#2C181020;color:#2C1810;"><i class="fas fa-chart-line"></i></div>
                <div class="kpi-valor">${{ number_format($ticketPromedio, 0, ',', '.') }}</div>
                <div class="kpi-label">Ticket promedio</div>
            </div>
        </div>
    </div>

    @if($ganancia['lineasSinCosto'] > 0)
    <div class="alert alert-warning py-2 small">
        <i class="fas fa-triangle-exclamation me-1"></i>
        {{ $ganancia['lineasSinCosto'] }} de {{ $ganancia['totalLineas'] }} líneas vendidas en este período
        son de fragancias sin "costo de elaboración por ml" configurado — se calcularon con costo $0,
        así que la ganancia real de esos productos es menor a la mostrada aquí. Complétalo en la ficha
        de cada fragancia para que el reporte sea exacto.
    </div>
    @endif

    <div class="alert alert-secondary py-2 small">
        <i class="fas fa-truck me-1"></i>
        La "Ganancia bruta" de arriba ya descuenta el costo REAL de envío de este período:
        {{ $pedidosPagados }} pedido(s) pagado(s) × ${{ number_format($costoEnvioReal, 2) }}
        = <strong>${{ number_format($ganancia['costoEnvioTotal'], 2) }}</strong>.
        Ese monto por pedido es editable en
        <a href="{{ route('admin.configuracion.index') }}">Admin &gt; Configuración &gt; Envío</a>.
    </div>

    @if($ganancia['lineasEstimadas'] > 0)
    <div class="alert alert-secondary py-2 small">
        <i class="fas fa-clock-rotate-left me-1"></i>
        {{ $ganancia['lineasEstimadas'] }} de {{ $ganancia['totalLineas'] }} líneas de este período son de
        pedidos vendidos <strong>antes</strong> de que el sistema empezara a guardar el costo real de cada
        venta — para esas se usó el costo <em>actual</em> del producto como aproximación, así que si has
        cambiado costos desde entonces, ese número puede no ser exacto. Las ventas nuevas ya quedan con su
        costo "congelado" al momento de la venta, así que este aviso va a ir desapareciendo con el tiempo.
    </div>
    @endif

    @if($ganancia['ivaMonto'] > 0)
    <div class="alert alert-info py-2 small">
        <i class="fas fa-circle-info me-1"></i>
        @if($iva['incluido'])
            Tus precios incluyen IVA ({{ $iva['porcentaje'] }}%), así que del ${{ number_format($ganancia['ingresos'], 0, ',', '.') }}
            que pagaron los clientes, ${{ number_format($ganancia['ivaMonto'], 0, ',', '.') }} es IVA que hay que
            declarar (no es ganancia tuya) — la "Ganancia bruta" de arriba ya sale calculada sobre el ingreso
            neto (${{ number_format($ganancia['ingresosNetos'], 0, ',', '.') }}), después de descontar el IVA.
        @else
            Tus precios NO incluyen IVA — se cobra aparte, encima del precio, al momento del checkout.
            Sobre los ${{ number_format($ganancia['ingresosNetos'], 0, ',', '.') }} en precios de producto de este
            período, se cobró ${{ number_format($ganancia['ivaMonto'], 0, ',', '.') }} de IVA ({{ $iva['porcentaje'] }}%)
            aparte, para un total de ${{ number_format($ganancia['ingresos'], 0, ',', '.') }} — ese IVA hay que
            declararlo al SRI, no es ganancia tuya. La "Ganancia bruta" de arriba ya está calculada sobre el
            ingreso neto, sin ese IVA.
        @endif
    </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#4A252020;color:#4A2520;"><i class="fas fa-percent"></i></div>
                <div class="kpi-valor">{{ number_format($tasaConversionPago, 1, ',', '.') }}%</div>
                <div class="kpi-label">Conversión de pago</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#dc354520;color:#dc3545;"><i class="fas fa-ban"></i></div>
                <div class="kpi-valor">{{ number_format($tasaCancelacion, 1, ',', '.') }}%</div>
                <div class="kpi-label">Tasa de cancelación ({{ $pedidosCancelados }} cancelados)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#19875420;color:#198754;"><i class="fas fa-check-circle"></i></div>
                <div class="kpi-valor">{{ $pedidosPagados }}</div>
                <div class="kpi-label">Pedidos pagados en el período</div>
            </div>
        </div>
    </div>

    <div class="admin-card mb-4">
        <div class="admin-card-header">
            <h5>Comparativo vs período anterior ({{ $desdeAnterior->format('d/m/Y') }} — {{ $hastaAnterior->format('d/m/Y') }})</h5>
        </div>
        <div class="admin-card-body">
            <div class="mb-3" style="height:260px;">
                <canvas id="chartComparativo"></canvas>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="d-flex justify-content-between align-items-center border rounded p-3">
                        <div>
                            <div class="small text-muted">Ventas pagadas</div>
                            <div class="fw-700">${{ number_format($ventasAnterior, 0, ',', '.') }}</div>
                        </div>
                        <span class="badge {{ $variacionVentasPct >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $variacionVentasPct >= 0 ? '+' : '' }}{{ number_format($variacionVentasPct, 1, ',', '.') }}%
                        </span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex justify-content-between align-items-center border rounded p-3">
                        <div>
                            <div class="small text-muted">Ganancia bruta</div>
                            <div class="fw-700">${{ number_format($gananciaAnterior['ganancia'], 0, ',', '.') }}</div>
                        </div>
                        <span class="badge {{ $variacionGananciaPct >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $variacionGananciaPct >= 0 ? '+' : '' }}{{ number_format($variacionGananciaPct, 1, ',', '.') }}%
                        </span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex justify-content-between align-items-center border rounded p-3">
                        <div>
                            <div class="small text-muted">Pedidos</div>
                            <div class="fw-700">{{ $pedidosAnterior }}</div>
                        </div>
                        <span class="badge {{ $variacionPedidosPct >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $variacionPedidosPct >= 0 ? '+' : '' }}{{ number_format($variacionPedidosPct, 1, ',', '.') }}%
                        </span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex justify-content-between align-items-center border rounded p-3">
                        <div>
                            <div class="small text-muted">Ticket promedio</div>
                            <div class="fw-700">${{ number_format($ticketPromedioAnterior, 0, ',', '.') }}</div>
                        </div>
                        <span class="badge {{ $variacionTicketPct >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $variacionTicketPct >= 0 ? '+' : '' }}{{ number_format($variacionTicketPct, 1, ',', '.') }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-7">
            <div class="admin-card">
                <div class="admin-card-header"><h5>Ventas por Día</h5></div>
                <div class="admin-card-body">
                    @if($ventasPorDia->count())
                    <div style="height:320px;" class="mb-3">
                        <canvas id="chartVentasDia"></canvas>
                    </div>
                    <div class="table-responsive" style="max-height:220px;overflow:auto;">
                        <table class="table table-sm align-middle mb-0 table-mobile-stack">
                            <thead><tr><th>Fecha</th><th>Pedidos</th><th>Total</th></tr></thead>
                            <tbody>
                            @foreach($ventasPorDia as $v)
                            <tr>
                                <td data-label="Fecha">{{ \Carbon\Carbon::parse($v->fecha)->format('d/m/Y') }}</td>
                                <td data-label="Pedidos">{{ $v->cantidad }}</td>
                                <td data-label="Total">${{ number_format($v->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-3 mb-0">No hay ventas registradas en este período.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="admin-card mb-3">
                <div class="admin-card-header"><h5>Pedidos por Estado</h5></div>
                <div class="admin-card-body">
                    @if($porEstado->count())
                    <div style="height:220px;" class="mb-3">
                        <canvas id="chartEstado"></canvas>
                    </div>
                    @endif
                    @forelse($porEstado as $estado => $cantidad)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-capitalize">{{ $estado }}</span>
                            <span class="badge bg-secondary">{{ $cantidad }}</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Sin datos en este período.</p>
                    @endforelse
                </div>
            </div>
            <div class="admin-card">
                <div class="admin-card-header"><h5>Método de Pago Más Usado</h5></div>
                <div class="admin-card-body">
                    @if($porMetodoPago->count())
                    <div style="height:220px;" class="mb-3">
                        <canvas id="chartMetodoPago"></canvas>
                    </div>
                    @endif
                    @forelse($porMetodoPago as $metodo => $cantidad)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-capitalize">{{ $metodo }}</span>
                            <span class="badge bg-secondary">{{ $cantidad }}</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Sin datos en este período.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const formatMoney = (value) => new Intl.NumberFormat('es-EC').format(value || 0);

const ventasDiaLabels = @json($ventasPorDia->map(fn($v) => \Carbon\Carbon::parse($v->fecha)->format('d/m')));
const ventasDiaTotales = @json($ventasPorDia->map(fn($v) => (float) $v->total));
const ventasDiaCantidad = @json($ventasPorDia->map(fn($v) => (int) $v->cantidad));

const estadoLabels = @json($porEstado->keys()->map(fn($e) => ucfirst($e))->values());
const estadoData = @json($porEstado->values());

const metodoLabels = @json($porMetodoPago->keys()->map(fn($m) => ucfirst($m))->values());
const metodoData = @json($porMetodoPago->values());

const comparativoActual = [{{ (float) $totalVentas }}, {{ (float) $ganancia['ganancia'] }}, {{ (int) $totalPedidos }}, {{ (float) $ticketPromedio }}];
const comparativoAnterior = [{{ (float) $ventasAnterior }}, {{ (float) $gananciaAnterior['ganancia'] }}, {{ (int) $pedidosAnterior }}, {{ (float) $ticketPromedioAnterior }}];

if (document.getElementById('chartVentasDia') && ventasDiaLabels.length) {
    new Chart(document.getElementById('chartVentasDia'), {
        type: 'line',
        data: {
            labels: ventasDiaLabels,
            datasets: [
                {
                    label: 'Ventas ($)',
                    data: ventasDiaTotales,
                    borderColor: '#8A6D1F',
                    backgroundColor: 'rgba(138,109,31,0.15)',
                    yAxisID: 'y',
                    tension: 0.3,
                    fill: true,
                },
                {
                    label: 'Pedidos',
                    data: ventasDiaCantidad,
                    borderColor: '#2C1810',
                    backgroundColor: 'rgba(44,24,16,0.15)',
                    yAxisID: 'y1',
                    tension: 0.3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    position: 'left',
                    ticks: { callback: (v) => '$' + formatMoney(v) }
                },
                y1: {
                    position: 'right',
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
}

if (document.getElementById('chartEstado') && estadoLabels.length) {
    new Chart(document.getElementById('chartEstado'), {
        type: 'doughnut',
        data: {
            labels: estadoLabels,
            datasets: [{
                data: estadoData,
                backgroundColor: ['#D4AF37', '#8B6F5E', '#2C1810', '#C4847A', '#dc3545', '#198754'],
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}

if (document.getElementById('chartMetodoPago') && metodoLabels.length) {
    new Chart(document.getElementById('chartMetodoPago'), {
        type: 'bar',
        data: {
            labels: metodoLabels,
            datasets: [{
                label: 'Cantidad de pedidos',
                data: metodoData,
                backgroundColor: '#4A2520'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
}

if (document.getElementById('chartComparativo')) {
    new Chart(document.getElementById('chartComparativo'), {
        type: 'bar',
        data: {
            labels: ['Ventas pagadas', 'Ganancia bruta', 'Pedidos', 'Ticket promedio'],
            datasets: [
                {
                    label: 'Período actual',
                    data: comparativoActual,
                    backgroundColor: '#8A6D1F'
                },
                {
                    label: 'Período anterior',
                    data: comparativoAnterior,
                    backgroundColor: '#C4A882'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });
}
</script>
@endsection
