<?php
namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\FraganciaTamano;
use App\Services\CostoRegaloCalculator;
use App\Services\SolicitudStockNotificador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarritoController extends Controller
{
    public function index()
    {
        $items = Auth::user()->carrito()->with(['fragancia', 'tamano'])->get();
        $subtotal = $items->sum(fn($i) => $i->tamano->precioFinal() * $i->cantidad);
        $regaloTotal = $items->sum(fn($i) => CostoRegaloCalculator::calcular($i->regalo_presentacion) * $i->cantidad);

        // Desglose de IVA sobre el subtotal (respeta si el precio guardado
        // ya incluye IVA o si hay que sumarlo aparte, ver Configuracion::desglosarIva).
        $iva = \App\Models\Configuracion::desglosarIva($subtotal);
        $subtotalConIva = $iva['conIva'];

        $total = $subtotalConIva + $regaloTotal;

        return view('app.front.carrito', compact('items', 'total', 'regaloTotal', 'subtotal', 'iva', 'subtotalConIva'));
    }

    public function agregar(Request $request)
    {
        $data = $request->validate([
            'fragancia_tamano_id' => 'required|exists:fragancia_tamanos,id',
            'cantidad'            => 'required|integer|min:1|max:50',
        ]);

        $tamano = FraganciaTamano::with('fragancia')->findOrFail($data['fragancia_tamano_id']);
        $fragancia = $tamano->fragancia;

        if (! $fragancia->activo) {
            return redirect()->back()->with('error', 'Esta fragancia ya no está disponible.');
        }

        $item = Carrito::where('user_id', Auth::id())
                       ->where('fragancia_tamano_id', $tamano->id)
                       ->first();

        $cantidadActual  = $item?->cantidad ?? 0;
        $cantidadDeseada = $cantidadActual + $data['cantidad'];

        if ($cantidadDeseada > $tamano->stock) {
            $disponible = max(0, $tamano->stock - $cantidadActual);
            $usuario = Auth::user();

            SolicitudStockNotificador::notificar($usuario, $fragancia, $tamano->id, $cantidadDeseada, $tamano->stock);

            return redirect()->back()->with(
                'error',
                $disponible > 0
                    ? "Solo quedan {$disponible} unidades disponibles de {$fragancia->nombre} ({$tamano->tamano})."
                    : "No hay más stock disponible de {$fragancia->nombre} ({$tamano->tamano}). Te avisaremos cuando llegue el producto."
            );
        }

        if ($item) {
            $item->increment('cantidad', $data['cantidad']);
        } else {
            Carrito::create([
                'user_id'             => Auth::id(),
                'fragancia_id'        => $fragancia->id,
                'fragancia_tamano_id' => $tamano->id,
                'cantidad'            => $data['cantidad'],
                'regalo_presentacion' => session('regalo_config.presentacion'),
            ]);
        }

        return redirect()->back()->with('success', 'Fragancia añadida al carrito.');
    }

    public function actualizar(Request $request, string $id)
    {
        $data = $request->validate([
            'cantidad' => 'required|integer|min:1|max:50',
            'regalo_presentacion' => 'nullable|in:blanco,negro',
        ]);

        $item = Carrito::with(['tamano', 'fragancia'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($data['cantidad'] > $item->tamano->stock) {
            return redirect()->back()->with(
                'error',
                "Solo quedan {$item->tamano->stock} unidades disponibles de {$item->fragancia->nombre} ({$item->tamano->tamano})."
            );
        }

        $update = ['cantidad' => $data['cantidad']];
        if (array_key_exists('regalo_presentacion', $data)) {
            $update['regalo_presentacion'] = $data['regalo_presentacion'];
        }

        $item->update($update);

        return redirect()->back()->with('success', 'Cantidad y opción de regalo actualizadas.');
    }

    public function quitar(string $id)
    {
        Carrito::where('id', $id)->where('user_id', Auth::id())->delete();
        return redirect()->back()->with('success', 'Elemento eliminado del carrito.');
    }

    public function vaciar(Request $request)
    {
        Auth::user()->carrito()->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Carrito vaciado.',
            ]);
        }

        return redirect()->route('carrito.index')->with('success', 'Carrito vaciado.');
    }
}
