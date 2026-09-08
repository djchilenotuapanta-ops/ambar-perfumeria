<?php
namespace Tests\Feature;

use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoTransicionEstadoTest extends TestCase
{
    use RefreshDatabase;

    private function pedido(string $estado = 'pendiente', string $pagoEstado = 'pendiente'): Pedido
    {
        $familia = Familia::create(['nombre' => 'Floral', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Test Parfum', 'casa_perfumista' => 'Ambar Maison',
            'descripcion' => 'desc', 'genero' => 'unisex', 'activo' => true,
            'familia_id' => $familia->id,
        ]);
        $tamano = $fragancia->tamanos()->create(['tamano' => '100 ml', 'precio' => 100000, 'stock' => 5]);

        $cliente = User::factory()->create(['role' => 'cliente']);

        $pedido = Pedido::create([
            'user_id' => $cliente->id, 'subtotal' => 100000, 'total' => 100000,
            'estado' => $estado, 'pago_estado' => $pagoEstado,
        ]);

        $pedido->detalles()->create([
            'fragancia_id' => $fragancia->id, 'fragancia_tamano_id' => $tamano->id,
            'nombre_fragancia' => $fragancia->nombre, 'tamano' => $tamano->tamano,
            'cantidad' => 1, 'precio_unitario' => 100000, 'subtotal' => 100000,
        ]);

        return $pedido;
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_pendiente_puede_pasar_a_entregado(): void
    {
        $pedido = $this->pedido(estado: 'pendiente');

        $this->actingAs($this->admin())
            ->patch(route('admin.pedidos.estado', $pedido->id), ['estado' => 'entregado'])
            ->assertSessionHas('success');

        $this->assertSame('entregado', $pedido->fresh()->estado);
    }

    public function test_pendiente_puede_pasar_a_cancelado(): void
    {
        $pedido = $this->pedido(estado: 'pendiente');

        $this->actingAs($this->admin())
            ->patch(route('admin.pedidos.estado', $pedido->id), ['estado' => 'cancelado']);

        $this->assertSame('cancelado', $pedido->fresh()->estado);
    }

    public function test_no_se_puede_cambiar_el_estado_de_un_pedido_ya_entregado(): void
    {
        $pedido = $this->pedido(estado: 'entregado');

        $this->actingAs($this->admin())
            ->patch(route('admin.pedidos.estado', $pedido->id), ['estado' => 'cancelado'])
            ->assertSessionHas('error');

        $this->assertSame('entregado', $pedido->fresh()->estado);
    }

    public function test_no_se_puede_cambiar_el_estado_de_un_pedido_ya_cancelado(): void
    {
        $pedido = $this->pedido(estado: 'cancelado');

        $this->actingAs($this->admin())
            ->patch(route('admin.pedidos.estado', $pedido->id), ['estado' => 'entregado'])
            ->assertSessionHas('error');

        $this->assertSame('cancelado', $pedido->fresh()->estado);
    }

    public function test_pago_estado_ya_no_acepta_reembolsado(): void
    {
        // El valor "reembolsado" fue eliminado del enum de la base de datos;
        // esto confirma que la validación del formulario lo rechaza.
        $pedido = $this->pedido(estado: 'entregado', pagoEstado: 'pagado');

        $this->actingAs($this->admin())
            ->patch(route('admin.pedidos.estado', $pedido->id), [
                'estado' => 'entregado',
                'pago_estado' => 'reembolsado',
            ])
            ->assertSessionHasErrors('pago_estado');

        $this->assertSame('pagado', $pedido->fresh()->pago_estado);
    }
}
