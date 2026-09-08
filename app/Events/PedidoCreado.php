<?php
namespace App\Events;

use App\Models\Pedido;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PedidoCreado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Pedido $pedido
    ) {}
}
