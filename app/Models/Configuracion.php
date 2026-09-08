<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracion extends Model
{
    protected $table = 'configuraciones';
    protected $fillable = ['clave', 'valor'];

    /**
     * Memoria estática por request: evita repetir el SELECT contra la
     * tabla `cache` cuando la misma clave se pide varias veces durante
     * la misma petición (p. ej. una vez por tarjeta de producto).
     */
    protected static array $memoria = [];

    public static function obtener(string $clave, $porDefecto = null)
    {
        if (array_key_exists($clave, self::$memoria)) {
            return self::$memoria[$clave];
        }

        return self::$memoria[$clave] = Cache::remember("config:{$clave}", 3600, function () use ($clave, $porDefecto) {
            return self::where('clave', $clave)->value('valor') ?? $porDefecto;
        });
    }

    public static function establecer(string $clave, $valor): void
    {
        self::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
        Cache::forget("config:{$clave}");
        unset(self::$memoria[$clave]);
    }

    public static function booleano($valor): bool
    {
        return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Porcentaje e "incluido en precio" de IVA vigentes, en un solo lugar,
     * para que todos los controladores/vistas usen exactamente la misma
     * fuente de verdad en vez de repetir el fallback en cada archivo.
     */
    public static function iva(): array
    {
        return [
            'porcentaje' => (float) self::obtener('iva_porcentaje', config('comercial.iva.porcentaje', 15)),
            'incluido'   => self::booleano(self::obtener('iva_incluido', config('comercial.iva.incluido_en_precio', false))),
        ];
    }

    /**
     * Desglosa un monto en base imponible + IVA + total con IVA, respetando
     * si el IVA vigente ya viene incluido en el precio guardado o si hay que
     * sumarlo aparte. Antes, carrito/checkout/ficha de producto siempre
     * SUMABAN el IVA encima del precio guardado sin fijarse en esta bandera;
     * si "iva_incluido" estaba activo (el valor por defecto), el precio ya
     * traía el IVA metido y esas vistas lo estaban duplicando visualmente,
     * sin que coincidiera con el total real cobrado.
     */
    public static function desglosarIva(float $monto): array
    {
        $iva = self::iva();

        $redondear = static fn (float $valor): float => (float) number_format($valor, 2, '.', '');

        if ($iva['incluido']) {
            $conIva = $monto;
            $base   = $iva['porcentaje'] > 0 ? $monto / (1 + $iva['porcentaje'] / 100) : $monto;
        } else {
            $base   = $monto;
            $conIva = $monto + ($monto * ($iva['porcentaje'] / 100));
        }

        $base = $redondear($base);
        $conIva = $redondear($conIva);

        return [
            'base'       => $base,
            'iva'        => $redondear($conIva - $base),
            'conIva'     => $conIva,
            'porcentaje' => $iva['porcentaje'],
            'incluido'   => $iva['incluido'],
        ];
    }

    /**
     * Recargo vigente por tamaño de presentación (ej.: 100 => 0, 50 => 0.15).
     * Lee primero lo guardado en esta tabla (editable desde el panel de
     * Configuración); si una talla no tiene valor guardado, usa el
     * default de config/comercial.php como semilla.
     */
    public static function recargosTamanos(): array
    {
        $recargos = [];
        foreach (config('comercial.tamanos', []) as $ml => $cfg) {
            $recargos[$ml] = (float) self::obtener("recargo_{$ml}", $cfg['recargo'] ?? 0);
        }
        return $recargos;
    }

    /**
     * Configuración vigente del margen de ganancia usado para el "precio
     * recomendado" del formulario de fragancias. Editable desde
     * Admin > Configuración; si no hay nada guardado, cae al default de
     * config/comercial.php.
     */
    public static function margenObjetivo(): array
    {
        $defaults = config('comercial.margen_objetivo', []);
        return [
            'modo'       => (string) self::obtener('margen_modo', $defaults['modo'] ?? 'porcentaje'),
            'porcentaje' => (float) self::obtener('margen_porcentaje', $defaults['porcentaje'] ?? 40),
            'valor_fijo' => (float) self::obtener('margen_valor_fijo', $defaults['valor_fijo'] ?? 8),
            'redondeo'   => (float) self::obtener('margen_redondeo', $defaults['redondeo'] ?? 1),
        ];
    }

    /**
     * Costo por ml ESTIMADO (ficticio) para una fragancia que todavía no
     * tiene costo_por_ml real cargado. Es la operación inversa de
     * calcularPrecioRecomendado() (ver fragancias/_form.blade.php): en vez
     * de partir del costo para sugerir un precio, partimos del precio_por_ml
     * ya guardado y "adivinamos" qué costo habría dejado el margen objetivo
     * configurado en Admin > Configuración (mismo modo/porcentaje/valor_fijo
     * que usa el formulario de fragancias).
     *
     * Se calcula sobre el frasco de referencia de 100 ml (sin recargo de
     * tamaño), descontando el IVA si el precio lo incluye, y restando el
     * costo de envase de ese tamaño+género antes de repartir entre los 100 ml.
     */
    public static function costoEstimadoPorMl(float $precioPorMl, string $genero): float
    {
        if ($precioPorMl <= 0) {
            return 0;
        }

        $margen = self::margenObjetivo();

        $ivaPorcentaje = (float) self::obtener('iva_porcentaje', config('comercial.iva.porcentaje', 15));
        $ivaIncluido   = self::booleano(self::obtener('iva_incluido', config('comercial.iva.incluido_en_precio', false)));

        $precioConIva100 = $precioPorMl * 100;
        $precioSinIva100 = $ivaIncluido ? ($precioConIva100 / (1 + $ivaPorcentaje / 100)) : $precioConIva100;

        if ($margen['modo'] === 'valor_fijo') {
            $costoTotal100 = $precioSinIva100 - $margen['valor_fijo'];
        } else {
            $margenFraccion = min(max($margen['porcentaje'], 0), 95) / 100;
            $costoTotal100 = $precioSinIva100 * (1 - $margenFraccion);
        }

        $costoEnvase100 = \App\Models\PresentacionEnvase::costoPara(100, $genero);
        $costoPorMl = ($costoTotal100 - $costoEnvase100) / 100;

        return round(max($costoPorMl, 0), 4);
    }
}
