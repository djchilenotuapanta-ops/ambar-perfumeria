<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Clientes — Ambar Parfums</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #2C1810; margin: 0; }
        .header { border-bottom: 3px solid #D4AF37; padding-bottom: 12px; margin-bottom: 18px; }
        .header h1 { font-size: 20px; margin: 0 0 4px 0; color: #2C1810; }
        .header p { margin: 0; color: #8B6F5E; font-size: 11px; }
        .kpis { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .kpis td { width: 33.3%; padding: 10px; border: 1px solid #E5DDD3; text-align: center; }
        .kpi-valor { font-size: 18px; font-weight: bold; color: #2C1810; }
        .kpi-label { font-size: 9px; color: #8B6F5E; margin-top: 2px; }
        h2 { font-size: 13px; color: #2C1810; border-bottom: 1px solid #E5DDD3; padding-bottom: 4px; margin-top: 24px; margin-bottom: 8px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.datos th { background: #FAF6F0; text-align: left; padding: 6px 8px; border: 1px solid #E5DDD3; font-size: 10px; }
        table.datos td { padding: 6px 8px; border: 1px solid #E5DDD3; font-size: 10px; }
        .sin-datos { color: #8B6F5E; font-style: italic; padding: 8px 0; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #E5DDD3; font-size: 9px; color: #8B6F5E; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Ambar Parfums — Reporte de Clientes</h1>
        <p>Distribución y comportamiento de la base de clientes &nbsp;|&nbsp; Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="kpis">
        <tr>
            <td>
                <div class="kpi-valor">{{ $totalClientes }}</div>
                <div class="kpi-label">Clientes registrados</div>
            </td>
            <td>
                <div class="kpi-valor">{{ $clientesNuevos }}</div>
                <div class="kpi-label">Nuevos en los últimos 30 días</div>
            </td>
            <td>
                <div class="kpi-valor">{{ $clientesConCompras }}</div>
                <div class="kpi-label">Clientes con compras</div>
            </td>
        </tr>
    </table>

    <h2>Mejores clientes (gasto histórico)</h2>
    @if($mejoresClientes->count())
    <table class="datos">
        <thead>
            <tr><th>#</th><th>Cliente</th><th>Correo</th><th>Pedidos</th><th>Total gastado</th></tr>
        </thead>
        <tbody>
            @foreach($mejoresClientes as $i => $cliente)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $cliente->name }}</td>
                <td>{{ $cliente->email }}</td>
                <td>{{ $cliente->pedidos_count }}</td>
                <td>${{ number_format((float) ($cliente->total_gastado ?? 0), 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="sin-datos">Aún no hay clientes con compras registradas.</p>
    @endif

    <div class="footer">
        Ambar Parfums — Reporte generado automáticamente por el sistema de administración.
    </div>

</body>
</html>
