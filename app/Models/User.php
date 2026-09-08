<?php
namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function perfil()       { return $this->hasOne(PerfilCliente::class); }
    public function pedidos()      { return $this->hasMany(Pedido::class); }
    public function carrito()      { return $this->hasMany(Carrito::class); }

    public function esAdmin()      { return $this->role === UserRole::Admin->value; }
    public function esCliente()    { return $this->role === UserRole::Cliente->value; }

    public function tieneAccesoAdmin() {
        return $this->role === UserRole::Admin->value;
    }

    public function sendPasswordResetNotification($token)
    {
        \App\Services\NotificacionSegura::enviar($this, new \App\Notifications\ResetPasswordNotification($token));
    }
}
