<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{
    protected $table    = 'pedido_detalles';
    protected $fillable = [
        'pedido_id',
        'fragancia_id',
        'fragancia_tamano_id',
        'nombre_fragancia',
        'tamano',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'regalo_presentacion',
        'regalo_costo',
        'costo_por_ml_snapshot',
        'costo_envase_snapshot',
    ];

    public function pedido()    { return $this->belongsTo(Pedido::class); }
    public function fragancia() { return $this->belongsTo(Fragancia::class); }
    public function tamano()    { return $this->belongsTo(FraganciaTamano::class, 'fragancia_tamano_id'); }
}
