<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fragancia;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\User;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    /**
     * Líneas de pedido pagadas dentro de un rango de fechas, con la
     * fragancia relacionada (para poder calcular costo y ganancia).
     */
    private function lineasVendidas(\Carbon\Carbon $desde, \Carbon\Carbon $hasta)
    {
        return PedidoDetalle::whereHas('pedido', function ($q) use ($desde, $hasta) {
                $q->whereBetween('created_at', [$desde, $hasta])
                  ->where('pago_estado', 'pagado');
            })
            ->with('fragancia:id,genero,costo_por_ml')
            ->get([
                'id', 'pedido_id', 'fragancia_id', 'nombre_fragancia', 'tamano', 'cantidad', 'subtotal', 'regalo_costo',
                'costo_por_ml_snapshot', 'costo_envase_snapshot',
            ]);
    }

    /**
     * Costo (COGS) de una línea de pedido: costo de elaboración escalado al
     * tamaño vendido + costo de envase (según tamaño y género de la
     * fragancia) + costo de regalo si aplicó, todo multiplicado por la
     * cantidad vendida.
     *
     * Usa el snapshot de costo guardado al momento de la venta
     * (costo_por_ml_snapshot / costo_envase_snapshot) cuando existe, así el
     * número no cambia después si editas el costo del producto. Para
     * pedidos vendidos ANTES de que existiera ese snapshot, cae al costo
     * ACTUAL de la fragancia como aproximación (igual que hacía antes) —
     * $esEstimado se marca en true para poder avisarlo en el reporte.
     */
    private function costoLinea(PedidoDetalle $detalle, array $costosEnvase, bool &$esEstimado = null): float
    {
        $ml = (int) preg_replace('/[^0-9]/', '', (string) $detalle->tamano);
        $tieneSnapshot = $detalle->costo_por_ml_snapshot !== null && $detalle->costo_envase_snapshot !== null;
        $esEstimado = ! $tieneSnapshot;

        if ($tieneSnapshot) {
            $costoPorMl = (float) $detalle->costo_por_ml_snapshot;
            $costoEnvase = (float) $detalle->costo_envase_snapshot;
        } else {
            $fragancia = $detalle->fragancia;
            $costoPorMl = (float) ($fragancia->costo_por_ml ?? 0);
            $genero = in_array($fragancia->genero ?? null, ['hombre', 'mujer'], true) ? $fragancia->genero : 'unisex';
            $costoEnvase = $costosEnvase["{$ml}|{$genero}"] ?? 0;
        }

        return ($costoPorMl * $ml + $costoEnvase) * $detalle->cantidad + (float) $detalle->regalo_costo;
    }

    /**
     * Configuración vigente de IVA para descontarlo del ingreso antes de
     * calcular la ganancia real.
     */
    private function ivaVigente(): array
    {
        // Delegado a Configuracion::iva() para que exista una única fuente
        // de verdad del IVA vigente (misma que usan carrito/checkout/ficha
        // de producto vía Configuracion::desglosarIva()).
        return \App\Models\Configuracion::iva();
    }

    /**
     * Ganancia bruta de un conjunto de líneas ya vendidas: ingresos por
     * producto (subtotal) menos el costo de esas líneas, y menos el costo
     * REAL de envío del período (lo que se le paga al courier, no lo que
     * se le cobra al cliente — ver Admin > Configuración > Envío).
     *
     * Importante: el IVA NUNCA es ganancia — es plata que hay que declarar
     * y entregar al SRI, se cobre incluido en el precio o aparte encima en
     * el checkout (ambos casos son configurables y dinámicos desde
     * Admin > Configuración > Impuestos, sin nada hardcodeado aquí: el
     * porcentaje y el modo "incluido/aparte" se leen en vivo vía
     * Configuracion::desglosarIva(), la misma función que usan
     * carrito/checkout, así el reporte nunca se desalinea de lo que
     * realmente se le cobró al cliente). La ganancia se calcula siempre
     * sobre el ingreso neto (sin IVA), no sobre lo que pagó el cliente.
     * No descuenta otros gastos operativos (solo costo de producto, IVA y
     * envío real).
     *
     * @param array $iva Se mantiene como parámetro para no romper las
     *   llamadas existentes y para que quien llame también tenga a mano
     *   'incluido'/'porcentaje' vigentes (p. ej. para las vistas), pero
     *   el cálculo de IVA en sí ya no depende de este valor: se recalcula
     *   internamente vía Configuracion::desglosarIva() para los dos modos.
     */
    private function calcularGanancia(\Illuminate\Support\Collection $lineas, array $costosEnvase, array $iva, float $costoEnvioTotal = 0.0): array
    {
        $costoTotal = 0.0;
        $lineasSinCosto = 0;
        $lineasEstimadas = 0; // vendidas antes de guardar el snapshot de costo

        foreach ($lineas as $detalle) {
            $esEstimado = null;
            $costoTotal += $this->costoLinea($detalle, $costosEnvase, $esEstimado);
            if ($esEstimado) {
                $lineasEstimadas++;
            }

            $costoPorMlUsado = $detalle->costo_por_ml_snapshot !== null
                ? (float) $detalle->costo_por_ml_snapshot
                : (float) ($detalle->fragancia->costo_por_ml ?? 0);
            if ($costoPorMlUsado <= 0) {
                $lineasSinCosto++;
            }
        }

        // Monto guardado en cada línea (precio_unitario * cantidad). Si
        // "iva_incluido" está ON, este monto YA trae el IVA metido; si está
        // OFF, es el precio BASE y el IVA se cobra aparte encima en el
        // checkout (ver Configuracion::desglosarIva, misma lógica que usa
        // carrito/checkout, para que el reporte nunca se desalinee de lo
        // que en verdad se le cobró al cliente).
        $montoGuardado = (float) $lineas->sum('subtotal');
        $desglose = \App\Models\Configuracion::desglosarIva($montoGuardado);

        $ingresosConIva = $desglose['conIva']; // lo que pagó el cliente por producto (con IVA, se cobre incluido o aparte)
        $ingresosNetos  = $desglose['base'];   // base real del negocio, sin IVA
        $ivaMonto       = $desglose['iva'];    // IVA cobrado en el período, a declarar al SRI

        $ganancia = $ingresosNetos - $costoTotal - $costoEnvioTotal;
        $margenPorc = $ingresosNetos > 0 ? round(($ganancia / $ingresosNetos) * 100, 1) : 0.0;

        return [
            'ingresos'       => round($ingresosConIva, 2), // lo que pagó el cliente (con IVA si aplica)
            'ingresosNetos'  => round($ingresosNetos, 2),  // ingreso sin IVA, base real del negocio
            'ivaMonto'       => round($ivaMonto, 2),
            'costoTotal'     => round($costoTotal, 2),
            'costoEnvioTotal' => round($costoEnvioTotal, 2),
            'ganancia'       => round($ganancia, 2),       // ganancia YA neta de IVA, costo de producto y envío real
            'margenPorc'     => $margenPorc,
            'lineasSinCosto' => $lineasSinCosto,
            'lineasEstimadas' => $lineasEstimadas,
            'totalLineas'    => $lineas->count(),
        ];
    }


    private function calcularReporteVentas(\Carbon\Carbon $desde, \Carbon\Carbon $hasta): array
    {
        $pedidosPeriodo = Pedido::whereBetween('created_at', [$desde, $hasta]);
        $diasPeriodo = $desde->diffInDays($hasta) + 1;
        $desdeAnterior = (clone $desde)->subDays($diasPeriodo);
        $hastaAnterior = (clone $hasta)->subDays($diasPeriodo);
        $pedidosPeriodoAnterior = Pedido::whereBetween('created_at', [$desdeAnterior, $hastaAnterior]);

        $totalVentas   = (clone $pedidosPeriodo)->where('pago_estado', 'pagado')->sum('total');
        $totalPedidos  = (clone $pedidosPeriodo)->count();
        $pedidosPagados = (clone $pedidosPeriodo)->where('pago_estado', 'pagado')->count();
        $pedidosCancelados = (clone $pedidosPeriodo)->where('estado', 'cancelado')->count();
        $ticketPromedio = $pedidosPagados > 0
            ? (clone $pedidosPeriodo)->where('pago_estado', 'pagado')->avg('total')
            : 0;
        $tasaConversionPago = $totalPedidos > 0
            ? round(($pedidosPagados / $totalPedidos) * 100, 1)
            : 0;
        $tasaCancelacion = $totalPedidos > 0
            ? round(($pedidosCancelados / $totalPedidos) * 100, 1)
            : 0;

        $ventasAnterior = (clone $pedidosPeriodoAnterior)->where('pago_estado', 'pagado')->sum('total');
        $pedidosAnterior = (clone $pedidosPeriodoAnterior)->count();
        $pedidosPagadosAnterior = (clone $pedidosPeriodoAnterior)->where('pago_estado', 'pagado')->count();
        $ticketPromedioAnterior = $pedidosPagadosAnterior > 0
            ? (clone $pedidosPeriodoAnterior)->where('pago_estado', 'pagado')->avg('total')
            : 0;

        $variacionVentasPct = $ventasAnterior > 0
            ? round((($totalVentas - $ventasAnterior) / $ventasAnterior) * 100, 1)
            : ($totalVentas > 0 ? 100.0 : 0.0);
        $variacionPedidosPct = $pedidosAnterior > 0
            ? round((($totalPedidos - $pedidosAnterior) / $pedidosAnterior) * 100, 1)
            : ($totalPedidos > 0 ? 100.0 : 0.0);
        $variacionTicketPct = $ticketPromedioAnterior > 0
            ? round((($ticketPromedio - $ticketPromedioAnterior) / $ticketPromedioAnterior) * 100, 1)
            : ($ticketPromedio > 0 ? 100.0 : 0.0);

        $ventasPorDia = (clone $pedidosPeriodo)
            ->selectRaw('DATE(created_at) as fecha, SUM(total) as total, COUNT(*) as cantidad')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $porEstado = (clone $pedidosPeriodo)
            ->selectRaw('estado, COUNT(*) as cantidad')
            ->groupBy('estado')
            ->pluck('cantidad', 'estado');

        $porMetodoPago = (clone $pedidosPeriodo)
            ->whereNotNull('pago_metodo')
            ->selectRaw('pago_metodo, COUNT(*) as cantidad')
            ->groupBy('pago_metodo')
            ->orderByDesc('cantidad')
            ->pluck('cantidad', 'pago_metodo');

        // Ganancia bruta del período: ingresos por producto - costo de
        // elaboración y envase de lo vendido - costo REAL de envío de esos
        // pedidos (lo que se le paga al courier, no lo que se cobró al
        // cliente). Independiente del $totalVentas de arriba (que sí
        // incluye lo cobrado por envío) para no mezclar ingreso con costo.
        $costosEnvase = \App\Models\PresentacionEnvase::costosIndexados();
        $iva = $this->ivaVigente();
        $costoEnvioReal = (float) \App\Models\Configuracion::obtener('costo_envio_real', config('comercial.costo_envio_real', 5));
        $costoEnvioTotal = $costoEnvioReal * $pedidosPagados;
        $costoEnvioTotalAnterior = $costoEnvioReal * $pedidosPagadosAnterior;
        $ganancia = $this->calcularGanancia($this->lineasVendidas($desde, $hasta), $costosEnvase, $iva, $costoEnvioTotal);
        $gananciaAnterior = $this->calcularGanancia($this->lineasVendidas($desdeAnterior, $hastaAnterior), $costosEnvase, $iva, $costoEnvioTotalAnterior);
        $variacionGananciaPct = $gananciaAnterior['ganancia'] > 0
            ? round((($ganancia['ganancia'] - $gananciaAnterior['ganancia']) / $gananciaAnterior['ganancia']) * 100, 1)
            : ($ganancia['ganancia'] > 0 ? 100.0 : 0.0);

        return compact(
            'desde', 'hasta', 'desdeAnterior', 'hastaAnterior', 'totalVentas', 'totalPedidos',
            'ticketPromedio', 'pedidosPagados', 'pedidosCancelados', 'tasaConversionPago',
            'tasaCancelacion', 'ventasAnterior', 'pedidosAnterior', 'ticketPromedioAnterior',
            'variacionVentasPct', 'variacionPedidosPct', 'variacionTicketPct',
            'ventasPorDia', 'porEstado', 'porMetodoPago',
            'ganancia', 'gananciaAnterior', 'variacionGananciaPct', 'iva', 'costoEnvioReal'
        );
    }

    private function rangoFechas(Request $request): array
    {
        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ]);

        $desde = $request->filled('desde')
            ? \Carbon\Carbon::parse($request->desde)->startOfDay()
            : now()->subDays(29)->startOfDay();
        $hasta = $request->filled('hasta')
            ? \Carbon\Carbon::parse($request->hasta)->endOfDay()
            : now()->endOfDay();

        return [$desde, $hasta];
    }

    public function ventas(Request $request)
    {
        [$desde, $hasta] = $this->rangoFechas($request);

        return view('app.back.reportes.ventas', $this->calcularReporteVentas($desde, $hasta));
    }

    public function ventasPdf(Request $request)
    {
        [$desde, $hasta] = $this->rangoFechas($request);

        $datos = $this->calcularReporteVentas($desde, $hasta);

        $pdf = Pdf::loadView('app.back.reportes.ventas-pdf', $datos)
                   ->setPaper('a4', 'portrait');

        $nombreArchivo = 'reporte-ventas_' . $desde->format('Y-m-d') . '_a_' . $hasta->format('Y-m-d') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    private function calcularReporteProductos(\Carbon\Carbon $desde, \Carbon\Carbon $hasta): array
    {
        $costosEnvase = \App\Models\PresentacionEnvase::costosIndexados();
        $iva = $this->ivaVigente();
        $lineas = $this->lineasVendidas($desde, $hasta);

        $ventasPorFragancia = $lineas->groupBy('fragancia_id')
            ->map(function ($grupo) use ($costosEnvase, $iva) {
                $primero = $grupo->first();
                $costoTotal = 0.0;
                $sinCosto = 0;
                $conEstimado = 0;
                foreach ($grupo as $detalle) {
                    $esEstimado = null;
                    $costoTotal += $this->costoLinea($detalle, $costosEnvase, $esEstimado);
                    if ($esEstimado) {
                        $conEstimado++;
                    }
                    $costoPorMlUsado = $detalle->costo_por_ml_snapshot !== null
                        ? (float) $detalle->costo_por_ml_snapshot
                        : (float) ($detalle->fragancia->costo_por_ml ?? 0);
                    if ($costoPorMlUsado <= 0) {
                        $sinCosto++;
                    }
                }
                // Mismo desglose dinámico que en calcularGanancia(): respeta
                // si el IVA vigente viene incluido en el precio guardado o
                // se cobra aparte encima en el checkout.
                $desglosePorFragancia = \App\Models\Configuracion::desglosarIva((float) $grupo->sum('subtotal'));
                $ingresosConIva = $desglosePorFragancia['conIva'];
                $ingresosNetos  = $desglosePorFragancia['base'];
                $ganancia = $ingresosNetos - $costoTotal;

                return (object) [
                    'fragancia_id'      => $primero->fragancia_id,
                    'nombre_fragancia'  => $primero->nombre_fragancia,
                    'unidades_vendidas' => $grupo->sum('cantidad'),
                    'ingresos'          => round($ingresosConIva, 2),
                    'ingresos_netos'    => round($ingresosNetos, 2),
                    'costo'             => round($costoTotal, 2),
                    'ganancia'          => round($ganancia, 2),
                    'margen_pct'        => $ingresosNetos > 0 ? round(($ganancia / $ingresosNetos) * 100, 1) : 0.0,
                    'costo_sin_datos'   => $sinCosto > 0,
                    'costo_estimado'    => $conEstimado > 0,
                ];
            })
            ->sortByDesc('unidades_vendidas')
            ->values();

        $masVendidas = $ventasPorFragancia->take(10);

        $umbral = (int) \App\Models\Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5));

        $stockCritico = \App\Models\FraganciaTamano::where('stock', '<=', $umbral)
            ->whereHas('fragancia', fn ($q) => $q->where('activo', true))
            ->with('fragancia')
            ->orderBy('stock')
            ->get();

        $idsVendidos = $ventasPorFragancia->pluck('fragancia_id');
        $sinMovimiento = Fragancia::where('activo', true)
            ->whereNotIn('id', $idsVendidos)
            ->with('tamanos')
            ->orderBy('created_at')
            ->take(10)
            ->get();

        // Totales del período, para el resumen de ganancia arriba de la tabla.
        // Usa el mismo costo real de envío que el reporte de Ventas para
        // que la "ganancia" de ambos reportes coincida en el mismo período.
        $pedidosPagadosPeriodo = Pedido::whereBetween('created_at', [$desde, $hasta])
            ->where('pago_estado', 'pagado')->count();
        $costoEnvioReal = (float) \App\Models\Configuracion::obtener('costo_envio_real', config('comercial.costo_envio_real', 5));
        $resumenGanancia = $this->calcularGanancia($lineas, $costosEnvase, $iva, $costoEnvioReal * $pedidosPagadosPeriodo);

        return compact('desde', 'hasta', 'masVendidas', 'stockCritico', 'sinMovimiento', 'umbral', 'resumenGanancia', 'iva');
    }

    private function calcularReporteClientes(): array
    {
        $totalClientes = User::where('role', 'cliente')->count();
        $clientesConCompras = User::where('role', 'cliente')
            ->whereHas('pedidos')
            ->count();

        $mejoresClientes = User::where('role', 'cliente')
            ->withCount('pedidos')
            ->withSum(['pedidos as total_gastado' => function ($q) {
                $q->where('pago_estado', 'pagado');
            }], 'total')
            ->orderByDesc('total_gastado')
            ->take(10)
            ->get();

        $clientesNuevos = User::where('role', 'cliente')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return compact('totalClientes', 'clientesConCompras', 'mejoresClientes', 'clientesNuevos');
    }

    public function productos(Request $request)
    {
        [$desde, $hasta] = $this->rangoFechas($request);

        return view('app.back.reportes.productos', $this->calcularReporteProductos($desde, $hasta));
    }

    public function productosPdf(Request $request)
    {
        [$desde, $hasta] = $this->rangoFechas($request);

        $datos = $this->calcularReporteProductos($desde, $hasta);

        $pdf = Pdf::loadView('app.back.reportes.productos-pdf', $datos)
                   ->setPaper('a4', 'portrait');

        $nombreArchivo = 'reporte-productos_' . $desde->format('Y-m-d') . '_a_' . $hasta->format('Y-m-d') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    public function clientes()
    {
        return view('app.back.reportes.clientes', $this->calcularReporteClientes());
    }

    public function clientesPdf()
    {
        $datos = $this->calcularReporteClientes();

        $pdf = Pdf::loadView('app.back.reportes.clientes-pdf', $datos)
                   ->setPaper('a4', 'portrait');

        return $pdf->download('reporte-clientes_' . now()->format('Y-m-d') . '.pdf');
    }
}
