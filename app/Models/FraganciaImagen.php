<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FraganciaImagen extends Model
{
    protected $table    = 'fragancia_imagenes';
    protected $fillable = ['fragancia_id','ruta_imagen','orden'];

    public function fragancia() { return $this->belongsTo(Fragancia::class); }
}
