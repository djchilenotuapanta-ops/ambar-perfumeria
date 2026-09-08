<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Productos — Ambar Parfums</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #2C1810; margin: 0; }
        .header { border-bottom: 3px solid #D4AF37; padding-bottom: 12px; margin-bottom: 18px; }
        .header h1 { font-size: 20px; margin: 0 0 4px 0; color: #2C1810; }
        .header p { margin: 0; color: #8B6F5E; font-size: 11px; }
        h2 { font-size: 13px; color: #2C1810; border-bottom: 1px solid #E5DDD3; padding-bottom: 4px; margin-top: 24px; margin-bottom: 8px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.datos th { background: #FAF6F0; text-align: left; padding: 6px 8px; border: 1px solid #E5DDD3; font-size: 10px; }
        table.datos td { padding: 6px 8px; border: 1px solid #E5DDD3; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; color: #fff; }
        .badge-agotado { background: #2C1810; }
        .badge-critico { background: #C62828; }
        .badge-ok { background: #2E7D32; }
        .sin-datos { color: #8B6F5E; font-style: italic; padding: 8px 0; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #E5DDD3; font-size: 9px; color: #8B6F5E; text-align: center; }
        .pill { display: inline-block; background: #FAF6F0; border: 1px solid #E5DDD3; border-radius: 10px; padding: 3px 8px; margin: 2px; font-size: 9px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Ambar Parfums — Reporte de Productos</h1>
        <p>Período: {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}
           &nbsp;|&nbsp; Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <h2>Fragancias más vendidas</h2>
    @if($masVendidas->count())
    <p style="font-size:10px;color:#8B6F5E;margin:0 0 6px 0;">
        Ganancia total del top 10, sin IVA: <strong style="color:#2E7D32;">${{ number_format($masVendidas->sum('ganancia'), 0, ',', '.') }}</strong>
        @if($resumenGanancia['lineasSinCosto'] > 0)
        &nbsp;— ⚠ hay productos sin costo de elaboración configurado, su ganancia sale de menos.
        @endif
    </p>
    <table class="datos">
        <thead>
            <tr><th>#</th><th>Fragancia</th><th>Unidades</th><th>Ingresos</th><th>Costo</th><th>Ganancia</th><th>Margen</th></tr>
        </thead>
        <tbody>
            @foreach($masVendidas as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->nombre_fragancia }}{{ $p->costo_sin_datos ? ' *' : '' }}</td>
                <td>{{ $p->unidades_vendidas }}</td>
                <td>${{ number_format($p->ingresos, 0, ',', '.') }}</td>
                <td>${{ number_format($p->costo, 0, ',', '.') }}</td>
                <td>${{ number_format($p->ganancia, 0, ',', '.') }}</td>
                <td>{{ number_format($p->margen_pct, 1, ',', '.') }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($resumenGanancia['lineasSinCosto'] > 0)
    <p style="font-size:9px;color:#8B6F5E;">* sin costo de elaboración configurado en la ficha del producto.</p>
    @endif
    @else
    <p class="sin-datos">Sin ventas registradas en este período.</p>
    @endif

    <h2>Stock crítico ({{ $umbral }} unidades o menos)</h2>
    @if($stockCritico->count())
    <table class="datos">
        <thead>
            <tr><th>Fragancia</th><th>Stock actual</th></tr>
        </thead>
        <tbody>
            @foreach($stockCritico as $f)
            <tr>
                <td>{{ $f->fragancia->nombre }} <span class="text-muted small">({{ $f->tamano }})</span></td>
                <td>
                    @if($f->stock == 0)
                        <span class="badge badge-agotado">Agotado</span>
                    @else
                        <span class="badge badge-critico">{{ $f->stock }} unidades</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="sin-datos">Ninguna fragancia con stock crítico.</p>
    @endif

    <h2>Fragancias sin ventas en el período</h2>
    @if($sinMovimiento->count())
    <p style="color:#8B6F5E; font-size: 10px;">
        Estas fragancias activas no tuvieron ninguna venta en el rango seleccionado.
        Son candidatas a promoción o revisión de precio.
    </p>
    <p>
        @foreach($sinMovimiento as $f)
        <span class="pill">{{ $f->nombre }}</span>
        @endforeach
    </p>
    @else
    <p class="sin-datos">Todas las fragancias activas tuvieron ventas.</p>
    @endif

    <div class="footer">
        Ambar Parfums — Reporte generado automáticamente por el sistema de administración.
    </div>

</body>
</html>
