<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Fragancia extends Model
{
    protected $fillable = [
        'nombre','slug','descripcion','casa_perfumista',
        'genero','notas_salida','notas_corazon','notas_fondo',
        'imagen_principal','activo','familia_id',
        'precio_por_ml','precio_especial_por_ml','costo_por_ml',

    ];

    protected function casts(): array {
        return ['activo' => 'boolean'];
    }

    protected static function booted() {
        static::creating(function ($f) {
            if (empty($f->slug)) {
                $f->slug = Str::slug($f->nombre . '-' . $f->casa_perfumista);
            }

            if (is_null($f->precio)) {
                $f->precio = 0;
            }
        });
    }

    public function familia()    { return $this->belongsTo(Familia::class); }
    public function imagenes()   { return $this->hasMany(FraganciaImagen::class)->orderBy('orden'); }
    public function detalles()   { return $this->hasMany(PedidoDetalle::class); }
    public function tamanos()    { return $this->hasMany(FraganciaTamano::class)->orderByDesc('precio'); }
    public function resenas()    { return $this->hasMany(Resena::class)->orderByDesc('created_at'); }

    public function sincronizarAgregados(): void
    {
        $tamanos = $this->tamanos()->get();

        $this->forceFill([
            'precio' => $tamanos->isNotEmpty() ? $tamanos->min('precio') : 0,
            'stock'  => $tamanos->sum('stock'),
        ])->saveQuietly();
    }

    public function precioFinal(): float {

        $precios = $this->tamanos->map(fn ($t) => $t->precioFinal());
        return $precios->isNotEmpty() ? (float) $precios->min() : round((float) $this->precio, 2);
    }

    public function tamanoMasBarato(): ?FraganciaTamano {
        return $this->tamanos->sortBy(fn ($t) => $t->precioFinal())->first();
    }

    public function tieneDescuento(): bool {
        return $this->tamanos->contains(fn ($t) => $t->tieneDescuento());
    }

    public function precioAntesDescuento(): float {
        if ($this->tamanos->isNotEmpty()) {
            $tamano = $this->tamanos->sortBy(fn ($t) => $t->precioFinal())->first();
            return round((float) $tamano->precio, 2);
        }
        return round((float) $this->precio, 2);
    }

    public function descuentoPorcentaje(): int {
        $antes = $this->precioAntesDescuento();
        $ahora = $this->precioFinal();
        if ($antes <= 0 || $ahora >= $antes) {
            return 0;
        }
        return (int) round((($antes - $ahora) / $antes) * 100);
    }

    public function stockTotal(): int {
        return $this->tamanos->isNotEmpty() ? (int) $this->tamanos->sum('stock') : (int) $this->stock;
    }

    public function tieneAlgunStock(): bool {
        return $this->stockTotal() > 0;
    }

    public function tieneStockBajo(): bool {
        $total = $this->stockTotal();
        return $total > 0 && $total <= (int) \App\Models\Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5));
    }

    public function etiquetaGenero(): string {
        return match($this->genero) {
            'mujer'  => '🌸 Mujer',
            'hombre' => '🌲 Hombre',
            'unisex' => '✦ Unisex',
            default  => $this->genero,
        };
    }

    public function imagenMostrar(): string
    {
        if ($this->imagen_principal && Storage::disk('public')->exists($this->imagen_principal)) {
            return asset('storage/' . $this->imagen_principal);
        }

        return $this->imagenGenericaPorDefecto();
    }

    public function imagenMostrarTamano(?int $ml = null): string
    {
        if ($this->imagen_principal && Storage::disk('public')->exists($this->imagen_principal)) {
            return asset('storage/' . $this->imagen_principal);
        }

        $genero = in_array($this->genero, ['hombre', 'mujer']) ? $this->genero : 'unisex';
        $ml     = in_array($ml, [100, 50, 30], true) ? $ml : 100;

        return asset('storage/fragancias/generic/frasco_' . $genero . '_' . $ml . '.jpg');
    }

    protected function imagenGenericaPorDefecto(): string
    {
        $slug = $this->familia?->slugImagen() ?? 'nicho';
        $variante = ($slug === 'floral' || $this->id % 2 === 0) ? '_slim' : '';

        return asset('storage/fragancias/generic/frasco_' . $slug . $variante . '.png');
    }
}
