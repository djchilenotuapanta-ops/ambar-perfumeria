<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fragancia;
use App\Models\Pedido;
use App\Models\User;
use App\Models\FraganciaTamano;

class DashboardController extends Controller
{
    public function index()
    {
        $umbral = (int) \App\Models\Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5));

        $stats = [
            'total_fragancias' => Fragancia::count(),
            'total_pedidos'    => Pedido::count(),
            'total_usuarios'   => User::where('role', 'cliente')->count(),
            'pedidos_hoy'      => Pedido::whereDate('created_at', today())->count(),
            'ventas_mes'       => Pedido::whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->where('pago_estado', 'pagado')
                                        ->sum('total'),
            'stock_bajo'       => FraganciaTamano::where('stock', '<=', $umbral)
                                        ->whereHas('fragancia', fn ($q) => $q->where('activo', true))
                                        ->count(),
        ];

        $cantidadNuevas   = (int) config('comercial.nuevas_adquisiciones.cantidad', 5);
        $tituloNuevas     = config('comercial.nuevas_adquisiciones.titulo', 'Nuevas Adquisiciones');

        $pedidos_recientes = Pedido::with('user')->latest()->take(8)->get();
        $fragancias_top    = Fragancia::with('tamanos')->latest()->take($cantidadNuevas)->get();

        $stockCritico = FraganciaTamano::with('fragancia')
                        ->where('stock', '<=', $umbral)
                        ->whereHas('fragancia', fn ($q) => $q->where('activo', true))
                        ->orderBy('stock', 'asc')
                        ->take(8)
                        ->get();

        return view('app.back.dashboard', compact('stats', 'pedidos_recientes', 'fragancias_top', 'stockCritico', 'umbral', 'tituloNuevas'));
    }
}
