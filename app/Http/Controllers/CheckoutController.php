<?php
namespace App\Http\Controllers;

use App\Events\PedidoCreado;
use App\Events\StockBajoDetectado;
use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\User;
use App\Services\CostoRegaloCalculator;
use App\Services\SolicitudStockNotificador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    private function calcularEnvio(float $subtotalConDescuento): float
    {
        // Actualmente la tienda solo maneja envíos a nivel nacional; el costo
        // internacional se guarda en Configuración pero no se usa aquí todavía.
        $umbralEnvioGratis = (float) \App\Models\Configuracion::obtener(
            'envio_gratis_desde', config('comercial.envio_gratis_desde', 80)
        );
        $costoEnvio = (float) \App\Models\Configuracion::obtener(
            'costo_envio_nacional', config('comercial.costo_envio_nacional', 5)
        );

        return $subtotalConDescuento >= $umbralEnvioGratis ? 0 : $costoEnvio;
    }

    public function show()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $items = $user->carrito()->with(['fragancia', 'tamano'])->get();

        if ($items->isEmpty()) {
            return redirect()->route('carrito.index')
                             ->with('error', 'Tu carrito está vacío. Agrega fragancias antes de continuar.');
        }

        foreach ($items as $item) {
            if (! $item->fragancia->activo || $item->cantidad > $item->tamano->stock) {
                return redirect()->route('carrito.index')->with(
                    'error',
                    "«{$item->fragancia->nombre} ({$item->tamano->tamano})» ya no tiene stock suficiente. Ajusta tu carrito antes de continuar."
                );
            }
        }

        $subtotal   = $items->sum(fn($i) => $i->tamano->precioFinal() * $i->cantidad);
        $descuento  = 0;
        $envio      = $this->calcularEnvio($subtotal - $descuento);
        $regaloConfig = session('regalo_config');
        $costoRegalo = $items->sum(fn($item) => CostoRegaloCalculator::calcular($item->regalo_presentacion) * $item->cantidad);

        // Desglose de IVA sobre el subtotal, respetando si el precio guardado
        // ya incluye IVA (Configuracion::desglosarIva). El total que se cobra
        // parte de subtotalConIva, no del subtotal "crudo", para que el IVA
        // no quede sumado dos veces cuando los precios ya lo incluyen, ni
        // omitido cuando no lo incluyen.
        $iva = \App\Models\Configuracion::desglosarIva($subtotal);
        $subtotalConIva = $iva['conIva'];
        $total = $subtotalConIva - $descuento + $envio + $costoRegalo;

        return view('app.front.checkout', compact(
            'items', 'subtotal', 'descuento', 'envio', 'costoRegalo', 'total', 'regaloConfig', 'iva', 'subtotalConIva'
        ));
    }

    public function procesar(Request $request)
    {
        $rules = [
            'direccion_envio' => ['required', 'string', 'max:255'],
            'ciudad_envio'    => ['required', 'string', 'in:' . implode(',', config('comercial.provincias'))],
            'telefono_entrega' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'pago_metodo'     => 'required|in:transferencia,contraentrega',
            'notas'           => 'nullable|string|max:500',
        ];

        $data = $request->validate($rules);

        $user  = Auth::user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        // Lock atómico por usuario: sin esto, un doble clic (antes de que
        // cargue el JS que deshabilita el botón) o un reintento de red podría
        // disparar dos peticiones casi simultáneas que lean el mismo carrito
        // y generen DOS pedidos duplicados con stock suficiente para ambos.
        // Con el lock, la segunda petición espera a que la primera termine
        // (máx. 10s) y al continuar encuentra el carrito ya vacío -> mensaje
        // correcto de "carrito vacío" en vez de un pedido duplicado.
        try {
            return Cache::lock('checkout:procesar:' . $user->id, 20)->block(10, function () use ($user, $data) {
                $items = $user->carrito()->with(['fragancia', 'tamano'])->get();

                if ($items->isEmpty()) {
                    return redirect()->route('carrito.index')
                                     ->with('error', 'Tu carrito está vacío. Agrega fragancias antes de continuar.');
                }

                try {
                    $pedido = DB::transaction(function () use ($user, $items, $data) {

                $tamanosFrescos = [];
                foreach ($items as $item) {
                    $tamano = $item->tamano()->lockForUpdate()->first();

                    if (! $item->fragancia->activo || $item->cantidad > $tamano->stock) {
                        SolicitudStockNotificador::notificar($user, $item->fragancia, $tamano->id, $item->cantidad, $tamano->stock);

                        throw new \RuntimeException(
                            "«{$item->fragancia->nombre} ({$tamano->tamano})» ya no tiene stock suficiente para completar el pedido."
                        );
                    }

                    $tamanosFrescos[$item->id] = $tamano;
                }

                $subtotal = $items->sum(
                    fn($i) => $tamanosFrescos[$i->id]->precioFinal() * $i->cantidad
                );
                $descuento    = 0;
                $envio        = $this->calcularEnvio($subtotal - $descuento);
                $regaloConfig = session('regalo_config');
                $costoRegalo  = $items->sum(fn($item) => CostoRegaloCalculator::calcular($item->regalo_presentacion) * $item->cantidad);
                $iva          = \App\Models\Configuracion::desglosarIva($subtotal);
                $total        = $iva['conIva'] - $descuento + $envio + $costoRegalo;
                $notasPedido  = trim((string) ($data['notas'] ?? ''));

                $hasGiftItems = $items->contains(fn($item) => !empty($item->regalo_presentacion));
                if (is_array($regaloConfig) && $hasGiftItems) {
                    $lineasRegalo = [];
                    $presentacion = [
                        'blanco' => 'Bolsa de regalo blanca',
                        'negro' => 'Bolsa de regalo negra',
                    ];

                    $lineasRegalo[] = 'REGALO:';
                    $lineasRegalo[] = 'Presentacion: ' . ($presentacion[$regaloConfig['presentacion'] ?? 'blanco'] ?? 'Bolsa de regalo blanca');
                    if (!empty($regaloConfig['ocasion'])) {
                        $lineasRegalo[] = 'Ocasion: ' . $regaloConfig['ocasion'];
                    }
                    if (!empty($regaloConfig['mensaje'])) {
                        $lineasRegalo[] = 'Mensaje: ' . $regaloConfig['mensaje'];
                    }
                    $lineasRegalo[] = 'Anonimo: ' . (!empty($regaloConfig['anonimo']) ? 'Si' : 'No');

                    $bloqueRegalo = implode(' | ', $lineasRegalo);
                    $notasPedido = $notasPedido !== ''
                        ? $notasPedido . ' || ' . $bloqueRegalo
                        : $bloqueRegalo;
                }

                $pedido = Pedido::create([
                    'user_id'         => $user->id,
                    'subtotal'        => $subtotal,
                    'descuento'       => $descuento,
                    'envio'           => $envio,
                    'total'           => $total,
                    'estado'          => 'pendiente',
                    'pago_estado'     => 'pendiente',
                    'pago_metodo'     => $data['pago_metodo'],
                    'direccion_envio' => $data['direccion_envio'],
                    'ciudad_envio'    => $data['ciudad_envio'],
                    'telefono_entrega' => $data['telefono_entrega'],
                    'notas'           => $notasPedido !== '' ? $notasPedido : null,
                ]);

                foreach ($items as $item) {
                    $tamano = $tamanosFrescos[$item->id];
                    $regaloCostoLinea = CostoRegaloCalculator::calcular($item->regalo_presentacion) * $item->cantidad;

                    // Snapshot del costo al momento de la venta (costo de
                    // elaboración por ml + costo de envase para este tamaño y
                    // género), para que el reporte de ganancias de este
                    // pedido no cambie después si editas el costo del
                    // producto más adelante.
                    $generoParaEnvase = in_array($item->fragancia->genero, ['hombre', 'mujer'], true)
                        ? $item->fragancia->genero
                        : 'unisex';
                    $costoEnvaseSnapshot = \App\Models\PresentacionEnvase::costoPara($tamano->ml(), $generoParaEnvase);

                    PedidoDetalle::create([
                        'pedido_id'              => $pedido->id,
                        'fragancia_id'           => $item->fragancia_id,
                        'fragancia_tamano_id'    => $tamano->id,
                        'nombre_fragancia'       => $item->fragancia->nombre,
                        'tamano'                 => $tamano->tamano,
                        'cantidad'               => $item->cantidad,
                        'precio_unitario'        => $tamano->precioFinal(),
                        'subtotal'               => $tamano->precioFinal() * $item->cantidad,
                        'regalo_presentacion'    => $item->regalo_presentacion,
                        'regalo_costo'           => $regaloCostoLinea,
                        'costo_por_ml_snapshot'  => $item->fragancia->costo_por_ml,
                        'costo_envase_snapshot'  => $costoEnvaseSnapshot,
                    ]);

                    $tamano->stock -= $item->cantidad;
                    $tamano->save();

                    $umbralStockBajo = (int) \App\Models\Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5));
                    if ($tamano->stock <= $umbralStockBajo && $item->fragancia->activo && ! $tamano->alerta_stock_enviada_at) {
                        StockBajoDetectado::dispatch($item->fragancia, $tamano->stock, $umbralStockBajo);
                        $tamano->alerta_stock_enviada_at = now();
                        $tamano->saveQuietly();
                    }
                }

                    Carrito::where('user_id', $user->id)->delete();

                    return $pedido;
                });

                } catch (\RuntimeException $e) {
                    return redirect()->route('carrito.index')->with('error', $e->getMessage());
                }

                PedidoCreado::dispatch($pedido);

                session()->forget('regalo_config');

                return redirect()->route('pedidos.confirmacion', $pedido->numero_pedido)
                                 ->with('success', '¡Tu pedido ha sido registrado exitosamente!');
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            return redirect()->route('carrito.index')
                             ->with('error', 'Ya hay un pedido en proceso para tu cuenta. Espera un momento e inténtalo de nuevo.');
        }
    }

    public function confirmacion(string $numeroPedido)
    {
        $pedido = Pedido::where('numero_pedido', $numeroPedido)
                        ->where('user_id', Auth::id())
                        ->with('detalles')
                        ->firstOrFail();

        return view('app.front.checkout-confirmacion', compact('pedido'));
    }

    public function subirComprobante(Request $request, string $numeroPedido)
    {
        $pedido = Pedido::where('numero_pedido', $numeroPedido)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        if ($pedido->pago_metodo !== 'transferencia') {
            return redirect()
                ->route('pedidos.confirmacion', $pedido->numero_pedido)
                ->with('error', 'Este pedido no requiere comprobante de transferencia.');
        }

        if ($pedido->pago_estado !== 'pendiente') {
            return redirect()
                ->route('pedidos.confirmacion', $pedido->numero_pedido)
                ->with('error', 'Este pedido ya no está pendiente de pago.');
        }

        $request->validate([
            'comprobante' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'comprobante.mimes' => 'El comprobante debe ser una imagen (JPG, PNG) o un PDF.',
            'comprobante.max'   => 'El archivo no puede pesar más de 5 MB.',
        ]);

        if ($pedido->comprobante_pago) {
            Storage::disk('public')->delete($pedido->comprobante_pago);
        }

        $ruta = $request->file('comprobante')->store('comprobantes/'.$pedido->numero_pedido, 'public');

        $pedido->update([
            'comprobante_pago' => $ruta,
            'comprobante_subido_at' => now(),
        ]);

        return redirect()
            ->route('pedidos.confirmacion', $pedido->numero_pedido)
            ->with('success', 'Comprobante recibido. Tu pago será confirmado por el equipo en cuanto lo revise.');
    }

    public function verComprobante(string $numeroPedido)
    {
        $pedido = Pedido::where('numero_pedido', $numeroPedido)->firstOrFail();

        if (Auth::check() && Auth::user()->role === 'cliente' && $pedido->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para ver este comprobante.');
        }

        if (! $pedido->comprobante_pago) {
            abort(404, 'No existe comprobante para este pedido.');
        }

        if (! Storage::disk('public')->exists($pedido->comprobante_pago)) {
            abort(404, 'El comprobante no está disponible.');
        }

        $extension = strtolower(pathinfo($pedido->comprobante_pago, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };

        return response()->file(Storage::disk('public')->path($pedido->comprobante_pago), [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($pedido->comprobante_pago) . '"',
        ]);
    }

    public function cancelar(string $numeroPedido)
    {
        $pedido = Pedido::where('numero_pedido', $numeroPedido)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        if ($pedido->pago_estado === 'pagado') {
            return redirect()
                ->route('pedidos.confirmacion', $pedido->numero_pedido)
                ->with('error', 'No puedes cancelar un pedido que ya fue pagado. Contacta a soporte.');
        }

        if ($pedido->estado === 'cancelado') {
            return redirect()
                ->route('pedidos.confirmacion', $pedido->numero_pedido)
                ->with('error', 'Este pedido ya está cancelado.');
        }

        $pedido->update(['estado' => 'cancelado']);

        return redirect()
            ->route('pedidos.confirmacion', $pedido->numero_pedido)
            ->with('success', 'Tu pedido ha sido cancelado.');
    }
}
