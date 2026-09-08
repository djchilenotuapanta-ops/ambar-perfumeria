<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FraganciaTamano extends Model
{

    protected $table    = 'fragancia_tamanos';
    protected $fillable = ['fragancia_id', 'tamano', 'precio', 'precio_especial', 'stock', 'alerta_stock_enviada_at'];

    protected function casts(): array
    {
        return ['alerta_stock_enviada_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $tamano) {
            // Si el stock vuelve a estar por encima del umbral, limpiamos la
            // marca de "ya se avisó" para que, si vuelve a bajar más
            // adelante, se genere una alerta nueva en vez de quedar
            // silenciada para siempre por un aviso antiguo.
            $umbral = (int) \App\Models\Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5));
            if ($tamano->stock > $umbral && $tamano->alerta_stock_enviada_at !== null) {
                $tamano->alerta_stock_enviada_at = null;
            }
        });

        static::saved(function (self $tamano) {
            $tamano->fragancia?->sincronizarAgregados();
        });
        static::deleted(function (self $tamano) {
            $tamano->fragancia?->sincronizarAgregados();
        });
    }

    public function fragancia() { return $this->belongsTo(Fragancia::class); }

    public function precioFinal(): float
    {

        if (!is_null($this->precio_especial) && $this->precio_especial < $this->precio) {
            return round((float) $this->precio_especial, 2);
        }
        return round((float) $this->precio, 2);
    }

    public function tieneDescuento(): bool
    {
        return !is_null($this->precio_especial) && $this->precio_especial < $this->precio;
    }

    public function tieneStock(int $cantidad = 1): bool
    {
        return $this->stock >= $cantidad;
    }

    public function ml(): int
    {
        return (int) preg_replace('/[^0-9]/', '', $this->tamano);
    }
}
