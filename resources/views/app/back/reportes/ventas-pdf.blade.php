<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas — Ambar Parfums</title>
    <style>
        /* dompdf no soporta Bootstrap ni flexbox/grid: todo el layout de
           este archivo se hace con CSS básico compatible con dompdf. */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #2C1810;
            margin: 0;
        }
        .header {
            border-bottom: 3px solid #D4AF37;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 4px 0;
            color: #2C1810;
        }
        .header p {
            margin: 0;
            color: #8B6F5E;
            font-size: 11px;
        }
        .kpis {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .kpis td {
            width: 25%;
            padding: 10px;
            border: 1px solid #E5DDD3;
            text-align: center;
            vertical-align: top;
        }
        .kpi-valor {
            font-size: 16px;
            font-weight: bold;
            color: #2C1810;
        }
        .kpi-label {
            font-size: 9px;
            color: #8B6F5E;
            margin-top: 2px;
        }
        .kpi-variacion {
            font-size: 9px;
            margin-top: 3px;
        }
        .positivo { color: #2E7D32; }
        .negativo { color: #C62828; }

        h2 {
            font-size: 13px;
            color: #2C1810;
            border-bottom: 1px solid #E5DDD3;
            padding-bottom: 4px;
            margin-top: 24px;
            margin-bottom: 8px;
        }
        table.datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.datos th {
            background: #FAF6F0;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #E5DDD3;
            font-size: 10px;
        }
        table.datos td {
            padding: 6px 8px;
            border: 1px solid #E5DDD3;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #E5DDD3;
            font-size: 9px;
            color: #8B6F5E;
            text-align: center;
        }
        .sin-datos {
            color: #8B6F5E;
            font-style: italic;
            padding: 8px 0;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Ambar Parfums — Reporte de Ventas</h1>
        <p>Período: {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}
           &nbsp;|&nbsp; Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-valor">${{ number_format($totalVentas, 0, ',', '.') }}</div>
                <div class="kpi-label">Ventas confirmadas (pagadas)</div>
                <div class="kpi-variacion {{ $variacionVentasPct >= 0 ? 'positivo' : 'negativo' }}">
                    {{ $variacionVentasPct >= 0 ? '▲' : '▼' }} {{ number_format(abs($variacionVentasPct), 1) }}% vs. período anterior
                </div>
            </td>
            <td>
                <div class="kpi-valor">${{ number_format($ganancia['ganancia'], 0, ',', '.') }}</div>
                <div class="kpi-label">Ganancia bruta, sin IVA ({{ $ganancia['margenPorc'] }}% margen)</div>
                <div class="kpi-variacion {{ $variacionGananciaPct >= 0 ? 'positivo' : 'negativo' }}">
                    {{ $variacionGananciaPct >= 0 ? '▲' : '▼' }} {{ number_format(abs($variacionGananciaPct), 1) }}% vs. período anterior
                </div>
            </td>
            <td>
                <div class="kpi-valor">{{ $totalPedidos }}</div>
                <div class="kpi-label">Pedidos en el período</div>
                <div class="kpi-variacion {{ $variacionPedidosPct >= 0 ? 'positivo' : 'negativo' }}">
                    {{ $variacionPedidosPct >= 0 ? '▲' : '▼' }} {{ number_format(abs($variacionPedidosPct), 1) }}% vs. período anterior
                </div>
            </td>
            <td>
                <div class="kpi-valor">${{ number_format($ticketPromedio, 0, ',', '.') }}</div>
                <div class="kpi-label">Ticket promedio</div>
                <div class="kpi-variacion {{ $variacionTicketPct >= 0 ? 'positivo' : 'negativo' }}">
                    {{ $variacionTicketPct >= 0 ? '▲' : '▼' }} {{ number_format(abs($variacionTicketPct), 1) }}% vs. período anterior
                </div>
            </td>
        </tr>
    </table>

    @if($ganancia['lineasSinCosto'] > 0)
    <p style="font-size:9px;color:#C62828;margin:-6px 0 8px 0;">
        ⚠ {{ $ganancia['lineasSinCosto'] }} de {{ $ganancia['totalLineas'] }} líneas vendidas en este período son de
        fragancias sin costo de elaboración configurado (se calcularon con costo $0), así que la ganancia real
        es menor a la mostrada.
    </p>
    @endif

    <p style="font-size:9px;color:#5C4A3D;margin:-6px 0 8px 0;">
        🚚 Ganancia ya descuenta el costo real de envío: {{ $pedidosPagados }} pedido(s) ×
        ${{ number_format($costoEnvioReal, 2) }} = ${{ number_format($ganancia['costoEnvioTotal'], 2) }}.
    </p>

    @if($ganancia['lineasEstimadas'] > 0)
    <p style="font-size:9px;color:#8B6F5E;margin:-6px 0 14px 0;">
        ⏱ {{ $ganancia['lineasEstimadas'] }} de {{ $ganancia['totalLineas'] }} líneas son de pedidos vendidos antes
        del histórico de costos por venta — se usó el costo actual del producto como aproximación.
    </p>
    @endif

    @if($ganancia['ivaMonto'] > 0)
    <p style="font-size:9px;color:#2C1810;margin:-6px 0 14px 0;">
        @if($iva['incluido'])
            ℹ Precios con IVA incluido ({{ $iva['porcentaje'] }}%): de ${{ number_format($ganancia['ingresos'], 0, ',', '.') }}
            cobrados, ${{ number_format($ganancia['ivaMonto'], 0, ',', '.') }} es IVA a declarar (no es ganancia).
        @else
            ℹ Precios sin IVA, cobrado aparte ({{ $iva['porcentaje'] }}%): sobre ${{ number_format($ganancia['ingresosNetos'], 0, ',', '.') }}
            en producto se cobró ${{ number_format($ganancia['ivaMonto'], 0, ',', '.') }} de IVA aparte (total ${{ number_format($ganancia['ingresos'], 0, ',', '.') }}), a declarar (no es ganancia).
        @endif
        La ganancia bruta ya está calculada sobre el ingreso neto de ${{ number_format($ganancia['ingresosNetos'], 0, ',', '.') }}.
    </p>
    @endif

    <table class="kpis">
        <tr>
            <td><div class="kpi-valor">{{ $tasaConversionPago }}%</div><div class="kpi-label">Tasa de conversión de pago</div></td>
            <td><div class="kpi-valor">{{ $pedidosPagados }}</div><div class="kpi-label">Pedidos pagados</div></td>
            <td><div class="kpi-valor">{{ $pedidosCancelados }}</div><div class="kpi-label">Pedidos cancelados</div></td>
            <td><div class="kpi-valor">{{ $tasaCancelacion }}%</div><div class="kpi-label">Tasa de cancelación</div></td>
        </tr>
    </table>

    <table class="kpis">
        <tr>
            <td><div class="kpi-valor">${{ number_format($ventasAnterior, 0, ',', '.') }}</div><div class="kpi-label">Ventas período anterior</div></td>
            <td><div class="kpi-valor">${{ number_format($gananciaAnterior['ganancia'], 0, ',', '.') }}</div><div class="kpi-label">Ganancia período anterior</div></td>
        </tr>
    </table>

    <h2>Ventas por día</h2>
    @if($ventasPorDia->count())
    <table class="datos">
        <thead>
            <tr><th>Fecha</th><th>Pedidos</th><th>Total vendido</th></tr>
        </thead>
        <tbody>
            @foreach($ventasPorDia as $dia)
            <tr>
                <td>{{ \Carbon\Carbon::parse($dia->fecha)->format('d/m/Y') }}</td>
                <td>{{ $dia->cantidad }}</td>
                <td>${{ number_format($dia->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="sin-datos">Sin ventas registradas en este período.</p>
    @endif

    <h2>Pedidos por estado</h2>
    @if($porEstado->count())
    <table class="datos">
        <thead>
            <tr><th>Estado</th><th>Cantidad de pedidos</th></tr>
        </thead>
        <tbody>
            @foreach($porEstado as $estado => $cantidad)
            <tr>
                <td>{{ ucfirst($estado) }}</td>
                <td>{{ $cantidad }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="sin-datos">Sin pedidos registrados en este período.</p>
    @endif

    <h2>Pedidos por método de pago</h2>
    @if($porMetodoPago->count())
    <table class="datos">
        <thead>
            <tr><th>Método de pago</th><th>Cantidad de pedidos</th></tr>
        </thead>
        <tbody>
            @foreach($porMetodoPago as $metodo => $cantidad)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $metodo)) }}</td>
                <td>{{ $cantidad }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="sin-datos">Sin pedidos con método de pago registrado en este período.</p>
    @endif

    <div class="footer">
        Ambar Parfums — Reporte generado automáticamente por el sistema de administración.
    </div>

</body>
</html>
