<?php
namespace Tests\Feature;

use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FraganciaEliminacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_permite_eliminar_fragancia_con_historial_de_pedidos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $familia = Familia::create(['nombre' => 'Floral', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Test Parfum', 'casa_perfumista' => 'Ambar Maison',
            'descripcion' => 'desc', 'genero' => 'unisex',
            'precio' => 100000, 'stock' => 10, 'activo' => true, 'familia_id' => $familia->id,
        ]);
        $cliente = User::factory()->create(['role' => 'cliente']);
        $pedido = Pedido::create([
            'user_id' => $cliente->id, 'subtotal' => 100000, 'total' => 100000,
        ]);
        PedidoDetalle::create([
            'pedido_id' => $pedido->id, 'fragancia_id' => $fragancia->id,
            'nombre_fragancia' => $fragancia->nombre, 'cantidad' => 1,
            'precio_unitario' => 100000, 'subtotal' => 100000,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.fragancias.destroy', $fragancia->id));

        $response->assertRedirect(route('admin.fragancias.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('fragancias', ['id' => $fragancia->id]);
    }

    public function test_permite_eliminar_fragancia_sin_pedidos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $familia = Familia::create(['nombre' => 'Floral', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Test Parfum', 'casa_perfumista' => 'Ambar Maison',
            'descripcion' => 'desc', 'genero' => 'unisex',
            'precio' => 100000, 'stock' => 10, 'activo' => true, 'familia_id' => $familia->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.fragancias.destroy', $fragancia->id));

        $response->assertRedirect(route('admin.fragancias.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('fragancias', ['id' => $fragancia->id]);
    }

    public function test_guarda_precio_por_ml_tal_como_lo_escribe_el_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $familia = Familia::create(['nombre' => 'Floral', 'activo' => true]);

        // El formulario de "Crear fragancia" pide el precio real por ml en
        // decimales (igual que la carga masiva por CSV), no el precio del
        // frasco completo: ver el rango de validación (min:0, max:20) y el
        // rango de "Sugerir por familia" (0.22-1.45) en _form.blade.php.
        $response = $this->actingAs($admin)->post(route('admin.fragancias.store'), [
            'nombre' => 'Fragancia Test',
            'descripcion' => 'Descripción de prueba',
            'casa_perfumista' => 'Casa Test',
            'genero' => 'unisex',
            'familia_id' => $familia->id,
            'precio_por_ml' => '0.63',
            'precio_especial_por_ml' => '0.55',
            'stock_100' => 10,
            'stock_50' => 5,
            'stock_30' => 3,
            'activo' => true,
        ]);

        $response->assertRedirect(route('admin.fragancias.index'));
        $this->assertDatabaseHas('fragancias', ['nombre' => 'Fragancia Test', 'precio_por_ml' => '0.63']);
        $this->assertDatabaseHas('fragancias', ['nombre' => 'Fragancia Test', 'precio_especial_por_ml' => '0.55']);
    }
}
