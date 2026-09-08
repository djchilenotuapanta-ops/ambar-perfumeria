<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Familia extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'icono', 'activo', 'es_premium'];

    protected function casts(): array {
        return ['activo' => 'boolean', 'es_premium' => 'boolean'];
    }

    public function fragancias() { return $this->hasMany(Fragancia::class); }

    private const COLORES = [
        'Floral'               => '#B0546C',
        'Maderado'             => '#78503C',
        'Oriental / Especiado' => '#96462C',
        'Fresco / Cítrico'     => '#336E66',
        'Acuático / Marino'    => '#285A82',
        'Oud / Medio Oriente'  => '#28190F',
        'Gourmand'             => '#78502D',
        'Nicho / Artesanal'    => '#3C3C46',
    ];

    public function color(): string
    {
        return self::COLORES[$this->nombre] ?? '#8B6F5E';
    }

    private const SLUGS_IMAGEN = [
        'Floral'               => 'floral',
        'Maderado'             => 'maderado',
        'Oriental / Especiado' => 'oriental',
        'Fresco / Cítrico'     => 'fresco',
        'Acuático / Marino'    => 'acuatico',
        'Oud / Medio Oriente'  => 'oud',
        'Gourmand'             => 'gourmand',
        'Nicho / Artesanal'    => 'nicho',
    ];

    public function slugImagen(): string
    {
        return self::SLUGS_IMAGEN[$this->nombre] ?? 'nicho';
    }
}
