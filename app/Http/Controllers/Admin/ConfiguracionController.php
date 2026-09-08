<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $topVentasTitulo = Configuracion::obtener('top_ventas_titulo', config('comercial.top_ventas.titulo', 'Las Más Vendidas'));

        $envioGratisDesde = Configuracion::obtener('envio_gratis_desde', config('comercial.envio_gratis_desde', 80));
        $costoEnvioNacional = Configuracion::obtener('costo_envio_nacional', config('comercial.costo_envio_nacional', 5));
        $costoEnvioReal = Configuracion::obtener('costo_envio_real', config('comercial.costo_envio_real', 5));
        $stockBajoUmbral = Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5));
        $mostrarStockBajoCliente = Configuracion::obtener('mostrar_stock_bajo_cliente', config('comercial.mostrar_stock_bajo_cliente', false));

        $bancoNombre = Configuracion::obtener('banco_nombre', config('comercial.cuenta_bancaria.banco'));
        $bancoTipo = Configuracion::obtener('banco_tipo', config('comercial.cuenta_bancaria.tipo'));
        $bancoNumero = Configuracion::obtener('banco_numero', config('comercial.cuenta_bancaria.numero'));
        $bancoTitular = Configuracion::obtener('banco_titular', config('comercial.cuenta_bancaria.titular'));
        $bancoRuc = Configuracion::obtener('banco_ruc', config('comercial.cuenta_bancaria.ruc'));

        $ivaPorcentaje = Configuracion::obtener('iva_porcentaje', config('comercial.iva.porcentaje', 15));
        $ivaIncluido = Configuracion::booleano(Configuracion::obtener('iva_incluido', config('comercial.iva.incluido_en_precio', false)));

        $margenObjetivo = Configuracion::margenObjetivo();

        // Recargo por tamaño de presentación (100/50/30 ml). Se guarda como
        // fracción (0.15) pero se muestra en el formulario como % (15).
        $recargosTamanos = [];
        foreach (config('comercial.tamanos', []) as $ml => $cfg) {
            $recargosTamanos[$ml] = round(Configuracion::recargosTamanos()[$ml] * 100, 2);
        }

        return view('app.back.configuracion.index', compact(
            'topVentasTitulo', 'envioGratisDesde', 'costoEnvioNacional', 'costoEnvioReal', 'stockBajoUmbral',
            'mostrarStockBajoCliente',
            'bancoNombre', 'bancoTipo', 'bancoNumero', 'bancoTitular', 'bancoRuc',
            'ivaPorcentaje', 'ivaIncluido', 'recargosTamanos', 'margenObjetivo'
        ));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'top_ventas_titulo' => 'required|string|max:60',
            'envio_gratis_desde' => 'required|numeric|min:0',
            'costo_envio_nacional' => 'required|numeric|min:0',
            'costo_envio_real' => 'required|numeric|min:0',
            'stock_bajo_umbral' => 'required|integer|min:0',
            'mostrar_stock_bajo_cliente' => 'nullable|boolean',
            'banco_nombre' => 'required|string|max:100',
            'banco_tipo' => 'required|string|max:60',
            'banco_numero' => 'required|string|max:40',
            'banco_titular' => 'required|string|max:150',
            'banco_ruc' => 'required|string|max:20',
            'iva_porcentaje' => 'required|numeric|min:0|max:100',
            'iva_incluido' => 'nullable|boolean',
            'margen_modo' => 'required|in:porcentaje,valor_fijo',
            'margen_porcentaje' => 'required|numeric|min:0|max:95',
            'margen_valor_fijo' => 'required|numeric|min:0',
            'margen_redondeo' => 'required|in:0.25,0.5,1,5,10',
            'recargo' => 'required|array',
            'recargo.*' => 'required|numeric|min:0|max:500',
        ]);

        Configuracion::establecer('top_ventas_titulo', $data['top_ventas_titulo']);
        Configuracion::establecer('envio_gratis_desde', $data['envio_gratis_desde']);
        Configuracion::establecer('costo_envio_nacional', $data['costo_envio_nacional']);
        Configuracion::establecer('costo_envio_real', $data['costo_envio_real']);
        Configuracion::establecer('stock_bajo_umbral', $data['stock_bajo_umbral']);
        $mostrarStockBajoValue = $request->has('mostrar_stock_bajo_cliente') ? '1' : '0';
        Configuracion::establecer('mostrar_stock_bajo_cliente', $mostrarStockBajoValue);
        Configuracion::establecer('banco_nombre', $data['banco_nombre']);
        Configuracion::establecer('banco_tipo', $data['banco_tipo']);
        Configuracion::establecer('banco_numero', $data['banco_numero']);
        Configuracion::establecer('banco_titular', $data['banco_titular']);
        Configuracion::establecer('banco_ruc', $data['banco_ruc']);

        // IVA
        Configuracion::establecer('iva_porcentaje', $data['iva_porcentaje']);
        $ivaIncluidoValue = $request->has('iva_incluido') ? '1' : '0';
        Configuracion::establecer('iva_incluido', $ivaIncluidoValue);

        // Margen de ganancia para el "precio recomendado" del formulario de
        // fragancias: el admin elige si prefiere trabajar con % de ganancia
        // o con un monto fijo (valor cerrado) sobre el frasco de 100 ml.
        Configuracion::establecer('margen_modo', $data['margen_modo']);
        Configuracion::establecer('margen_porcentaje', $data['margen_porcentaje']);
        Configuracion::establecer('margen_valor_fijo', $data['margen_valor_fijo']);
        Configuracion::establecer('margen_redondeo', $data['margen_redondeo']);

        // Recargo por tamaño: antes de guardar, avisamos (sin bloquear) si el
        // nuevo recargo dejaría a algún producto activo con margen negativo,
        // considerando AMBOS costos: elaboración (por ml, escala con el
        // tamaño) + envase (fijo por unidad, según tamaño+categoría del
        // producto, configurado en Admin > Envases).
        $avisosMargen = [];
        $costosEnvaseIndexados = \App\Models\PresentacionEnvase::costosIndexados();

        foreach (config('comercial.tamanos', []) as $ml => $cfg) {
            if (!array_key_exists((string) $ml, $data['recargo'])) {
                continue;
            }

            $nuevoRecargo = ((float) $data['recargo'][$ml]) / 100;
            $factor = 1 + $nuevoRecargo;

            $productosEnPerdida = 0;
            \App\Models\Fragancia::where('activo', true)
                ->whereNotNull('costo_por_ml')
                ->select('id', 'precio_por_ml', 'costo_por_ml', 'genero')
                ->chunk(200, function ($fragancias) use (&$productosEnPerdida, $ml, $factor, $costosEnvaseIndexados) {
                    foreach ($fragancias as $f) {
                        $costoEnvase = $costosEnvaseIndexados["{$ml}|{$f->genero}"] ?? 0;
                        $precioVenta = $f->precio_por_ml * $ml * $factor;
                        $costoTotal = ($f->costo_por_ml * $ml) + $costoEnvase;
                        if ($precioVenta < $costoTotal) {
                            $productosEnPerdida++;
                        }
                    }
                });

            if ($productosEnPerdida > 0) {
                $avisosMargen[] = "{$ml} ml: {$productosEnPerdida} producto(s) quedarían con margen negativo.";
            }

            Configuracion::establecer("recargo_{$ml}", $nuevoRecargo);
        }

        if (!empty($avisosMargen)) {
            return redirect()->route('admin.configuracion.index')
                ->with('warning', 'Configuración guardada, pero revisa esto: ' . implode(' ', $avisosMargen));
        }

        return redirect()->route('admin.configuracion.index')
                         ->with('success', 'Configuración actualizada correctamente.');
    }

    /**
     * Recalcula el precio y precio_especial de cada tamaño (100/50/30 ml)
     * de TODAS las fragancias, usando el recargo vigente. No toca stock
     * ni ningún otro dato del producto, solo los precios calculados.
     */
    public function recalcularPrecios()
    {
        $recargos = Configuracion::recargosTamanos();
        $tamanosConfig = config('comercial.tamanos', []);

        $fragancias = \App\Models\Fragancia::with('tamanos')->get();
        $productosActualizados = 0;
        $presentacionesActualizadas = 0;

        foreach ($fragancias as $fragancia) {
            $huboCambio = false;

            foreach ($tamanosConfig as $ml => $cfg) {
                $factor = 1 + ($recargos[$ml] ?? $cfg['recargo'] ?? 0);

                $precio = round($fragancia->precio_por_ml * $ml * $factor);
                $precioEspecial = $fragancia->precio_especial_por_ml
                    ? round($fragancia->precio_especial_por_ml * $ml * $factor)
                    : null;

                $afectadas = $fragancia->tamanos()
                    ->where('tamano', $ml . ' ml')
                    ->update([
                        'precio' => $precio,
                        'precio_especial' => $precioEspecial,
                    ]);

                if ($afectadas > 0) {
                    $presentacionesActualizadas += $afectadas;
                    $huboCambio = true;
                }
            }

            if ($huboCambio) {
                $productosActualizados++;
            }
        }

        return redirect()->route('admin.configuracion.index')
            ->with('success', "Precios recalculados: {$productosActualizados} fragancias ({$presentacionesActualizadas} presentaciones) actualizadas con el recargo vigente.");
    }
}
