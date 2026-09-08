<?php
namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidosHistorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_puede_ver_su_historial_de_pedidos(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);

        Pedido::create([
            'user_id' => $cliente->id,
            'subtotal' => 100000,
            'descuento' => 0,
            'envio' => 5,
            'total' => 100005,
            'estado' => 'pendiente',
            'pago_estado' => 'pendiente',
        ]);

        $response = $this->actingAs($cliente)->get(route('pedidos.historial'));

        $response->assertOk();
        $response->assertSee('Historial de Pedidos');
    }

    public function test_cliente_no_ve_pedidos_de_otros_usuarios_en_historial(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);
        $otro = User::factory()->create(['role' => 'cliente']);

        $pedidoAjeno = Pedido::create([
            'user_id' => $otro->id,
            'subtotal' => 150000,
            'descuento' => 0,
            'envio' => 5,
            'total' => 150005,
            'estado' => 'pendiente',
            'pago_estado' => 'pendiente',
        ]);

        $response = $this->actingAs($cliente)->get(route('pedidos.historial'));

        $response->assertOk();
        $response->assertDontSee($pedidoAjeno->numero_pedido);
    }
}
