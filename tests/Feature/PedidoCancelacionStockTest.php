<?php
namespace Tests\Feature;

use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\FraganciaTamano;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoCancelacionStockTest extends TestCase
{
    use RefreshDatabase;

    private function pedidoConDetalle(int $stockInicial, int $cantidadComprada): array
    {
        $familia = Familia::create(['nombre' => 'Floral', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Test Parfum', 'casa_perfumista' => 'Ambar Maison',
            'descripcion' => 'desc', 'genero' => 'unisex', 'activo' => true,
            'familia_id' => $familia->id,
        ]);
        $tamano = $fragancia->tamanos()->create([
            'tamano' => '100 ml', 'precio' => 100000, 'stock' => $stockInicial,
        ]);

        $cliente = User::factory()->create(['role' => 'cliente']);
        $pedido = Pedido::create([
            'user_id' => $cliente->id, 'subtotal' => 100000, 'total' => 100000,
            'estado' => 'pendiente', 'pago_estado' => 'pendiente',
        ]);
        PedidoDetalle::create([
            'pedido_id' => $pedido->id, 'fragancia_id' => $fragancia->id,
            'fragancia_tamano_id' => $tamano->id, 'nombre_fragancia' => $fragancia->nombre,
            'tamano' => $tamano->tamano, 'cantidad' => $cantidadComprada,
            'precio_unitario' => 100000, 'subtotal' => 100000 * $cantidadComprada,
        ]);

        return [$pedido, $tamano];
    }

    public function test_cancelar_pedido_restaura_el_stock(): void
    {
        [$pedido, $tamano] = $this->pedidoConDetalle(stockInicial: 5, cantidadComprada: 3);

        $pedido->update(['estado' => 'cancelado']);

        $this->assertSame(8, $tamano->fresh()->stock);
        $this->assertTrue($pedido->fresh()->stock_restaurado);
    }

    public function test_cancelar_pedido_no_duplica_la_restauracion_de_stock(): void
    {
        [$pedido, $tamano] = $this->pedidoConDetalle(stockInicial: 5, cantidadComprada: 3);

        $pedido->update(['estado' => 'cancelado']);
        $this->assertSame(8, $tamano->fresh()->stock);

        $pedido->fresh()->update(['estado' => 'pendiente']);
        $pedido->fresh()->update(['estado' => 'cancelado']);

        $this->assertSame(8, $tamano->fresh()->stock);
    }

    public function test_sincroniza_el_stock_agregado_de_la_fragancia_al_restaurar(): void
    {
        [$pedido, $tamano] = $this->pedidoConDetalle(stockInicial: 5, cantidadComprada: 3);

        $pedido->update(['estado' => 'cancelado']);

        $this->assertSame(8, $tamano->fragancia->fresh()->stock);
    }
}
