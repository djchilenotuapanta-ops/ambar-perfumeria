<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pedido extends Model
{
    protected $fillable = [
        'numero_pedido','user_id','subtotal','descuento','envio',
        'total','estado','pago_estado','pago_metodo',
        'direccion_envio','ciudad_envio','telefono_entrega','notas',
        'comprobante_pago','comprobante_subido_at',
    ];

    protected function casts(): array {
        return [
            'stock_restaurado' => 'boolean',
            'comprobante_subido_at' => 'datetime',
        ];
    }

    protected static function booted() {
        static::creating(function ($p) {
            if (empty($p->numero_pedido)) {

                $p->numero_pedido = 'TMP' . uniqid();
            }
        });

        static::created(function ($p) {
            if (str_starts_with($p->numero_pedido, 'TMP')) {
                $p->numero_pedido = 'PED-' . str_pad($p->id, 4, '0', STR_PAD_LEFT);
                $p->saveQuietly();
            }
        });
    }

    public function user()     { return $this->belongsTo(User::class); }
    public function detalles() { return $this->hasMany(PedidoDetalle::class); }

    /**
     * Calcula el desglose del IVA sobre el subtotal del pedido,
     * respetando la configuración vigente de IVA (incluido o no en el precio).
     */
    public function obtenerIva(): array
    {
        return Configuracion::desglosarIva($this->subtotal);
    }
}
