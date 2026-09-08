<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $table    = 'carrito';
    protected $fillable = ['user_id','fragancia_id','fragancia_tamano_id','cantidad','regalo_presentacion'];

    public function user()      { return $this->belongsTo(User::class); }
    public function fragancia() { return $this->belongsTo(Fragancia::class); }
    public function tamano()    { return $this->belongsTo(FraganciaTamano::class, 'fragancia_tamano_id'); }
}
