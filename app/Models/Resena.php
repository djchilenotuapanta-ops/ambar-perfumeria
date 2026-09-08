<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'resenas';

    protected $fillable = [
        'fragancia_id',
        'user_id',
        'calificacion',
        'comentario',
    ];

    protected function casts(): array
    {
        return [
            'calificacion' => 'integer',
        ];
    }

    public function fragancia()
    {
        return $this->belongsTo(Fragancia::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
