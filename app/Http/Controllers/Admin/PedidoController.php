<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('user')->latest()->paginate(20);
        return view('app.back.pedidos.index', compact('pedidos'));
    }

    public function show(string $id)
    {
        $pedido = Pedido::with(['user', 'detalles.fragancia'])->findOrFail($id);
        $iva = $pedido->obtenerIva();
        return view('app.back.pedidos.show', compact('pedido', 'iva'));
    }

    public function actualizarEstado(Request $request, string $id)
    {
        $pedido = Pedido::findOrFail($id);

        // Solo admin y solo si el pedido está en pendiente
        if ($pedido->estado !== 'pendiente') {
            return redirect()->back()->with('error', 'El pedido no está en estado pendiente.');
        }

        $data = $request->validate([
            'estado' => 'required|in:pendiente,entregado,cancelado',
            'pago_estado' => 'nullable|in:pendiente,pagado',
        ]);

        $cambios = [];

        // Siempre actualizar estado (fue validado arriba que está en pendiente)
        if ($data['estado'] !== $pedido->estado) {
            $cambios['estado'] = $data['estado'];
        }

        // Actualizar pago solo si se especifica y es diferente
        if (!empty($data['pago_estado']) && $data['pago_estado'] !== $pedido->pago_estado) {
            $cambios['pago_estado'] = $data['pago_estado'];
        }

        if (empty($cambios)) {
            return redirect()->back()->with('info', 'No hay cambios que guardar.');
        }

        // Guardar cambios
        $pedido->update($cambios);

        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
    }
}

