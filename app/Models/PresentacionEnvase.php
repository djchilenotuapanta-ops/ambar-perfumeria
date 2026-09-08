<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PresentacionEnvase extends Model
{
    protected $table = 'presentaciones_envases';
    protected $fillable = ['tamano', 'categoria', 'imagen', 'costo'];

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? route('storage.imagen', ['path' => $this->imagen]) : null;
    }

    public function eliminarImagen(): void
    {
        if ($this->imagen && Storage::disk('public')->exists($this->imagen)) {
            Storage::disk('public')->delete($this->imagen);
        }
    }

    /**
     * Costo de envase para un tamaño+categoría específico (ej.: 100, 'hombre').
     * Si no existe esa combinación todavía (no se ha subido/configurado),
     * devuelve 0 en vez de fallar.
     */
    public static function costoPara(int|string $tamano, string $categoria): float
    {
        return (float) self::where('tamano', (string) $tamano)
            ->where('categoria', $categoria)
            ->value('costo') ?? 0;
    }

    /**
     * Todos los costos de envase indexados como "tamano|categoria" => costo,
     * para no hacer una consulta por cada combinación al calcular márgenes.
     */
    public static function costosIndexados(): array
    {
        return self::all()->mapWithKeys(
            fn ($p) => ["{$p->tamano}|{$p->categoria}" => (float) $p->costo]
        )->all();
    }
}
