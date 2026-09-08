<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PerfilCliente extends Model
{
    protected $table    = 'perfiles_cliente';
    protected $fillable = ['user_id', 'foto'];

    public function user() { return $this->belongsTo(User::class); }
}
