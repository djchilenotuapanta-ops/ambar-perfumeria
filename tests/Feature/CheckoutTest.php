<?php
namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\PerfilCliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function clienteConCarrito(int $stock = 10, int $cantidad = 2): array
    {
        $user = User::factory()->create(['role' => 'cliente']);
        PerfilCliente::create(['user_id' => $user->id]);

        $familia = Familia::create(['nombre' => 'Floral', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Test Parfum', 'casa_perfumista' => 'Ambar Maison',
            'descripcion' => 'desc', 'genero' => 'unisex', 'activo' => true,
            'familia_id' => $familia->id,
        ]);

        $tamano = $fragancia->tamanos()->create([
            'tamano' => '100 ml', 'precio' => 100000, 'stock' => $stock,
        ]);

        Carrito::create([
            'user_id' => $user->id, 'fragancia_id' => $fragancia->id,
            'fragancia_tamano_id' => $tamano->id, 'cantidad' => $cantidad,
        ]);

        return [$user, $fragancia, $tamano];
    }

    public function test_checkout_crea_pedido_con_detalle(): void
    {
        [$user, $fragancia] = $this->clienteConCarrito(stock: 10, cantidad: 2);

        $response = $this->actingAs($user)->post(route('checkout.procesar'), [
            'direccion_envio' => 'Calle 123 #45-67',
            'ciudad_envio'    => 'Pichincha',
            'telefono_entrega' => '0991234567',
            'pago_metodo'     => 'transferencia',
        ]);

        $this->assertDatabaseHas('pedidos', [
            'user_id' => $user->id, 'estado' => 'pendiente', 'pago_estado' => 'pendiente',
        ]);
        $this->assertDatabaseHas('pedido_detalles', [
            'fragancia_id' => $fragancia->id, 'cantidad' => 2,
        ]);
        $response->assertRedirect();
    }

    public function test_checkout_descuenta_stock(): void
    {
        [$user, $fragancia, $tamano] = $this->clienteConCarrito(stock: 10, cantidad: 3);

        $this->actingAs($user)->post(route('checkout.procesar'), [
            'direccion_envio' => 'Calle 123', 'ciudad_envio' => 'Pichincha',
            'telefono_entrega' => '0991234567',
            'pago_metodo' => 'transferencia',
        ]);

        $this->assertEquals(7, $tamano->fresh()->stock);
        $this->assertEquals(7, $fragancia->fresh()->stock);
    }

    public function test_checkout_vacia_el_carrito(): void
    {
        [$user] = $this->clienteConCarrito();

        $this->actingAs($user)->post(route('checkout.procesar'), [
            'direccion_envio' => 'Calle 123', 'ciudad_envio' => 'Pichincha',
            'telefono_entrega' => '0991234567',
            'pago_metodo' => 'transferencia',
        ]);

        $this->assertEquals(0, $user->carrito()->count());
    }

    public function test_checkout_muestra_iva_separado_en_el_resumen(): void
    {
        // Se cambia vía Configuracion::establecer() (el mismo camino que usa
        // el panel Admin > Configuración) y no con config() directo, porque
        // Configuracion cachea en memoria estática por clave (self::$memoria)
        // y solo establecer() invalida esa caché; sobrescribir config()
        // a secas no tiene efecto si el valor ya se resolvió antes en el
        // mismo proceso.
        \App\Models\Configuracion::establecer('iva_porcentaje', '12');
        \App\Models\Configuracion::establecer('iva_incluido', '0');

        [$user] = $this->clienteConCarrito(stock: 10, cantidad: 1);

        $response = $this->actingAs($user)->get(route('checkout.show'));

        $response->assertOk();
        $response->assertSee('sin IVA');
        $response->assertSee('IVA');
        $response->assertSee('Las fragancias no incluyen IVA');
    }

    public function test_checkout_rechaza_si_no_hay_stock_suficiente(): void
    {
        [$user, $fragancia, $tamano] = $this->clienteConCarrito(stock: 1, cantidad: 1);

        $tamano->update(['stock' => 0]);

        $response = $this->actingAs($user)->post(route('checkout.procesar'), [
            'direccion_envio' => 'Calle 123', 'ciudad_envio' => 'Pichincha',
            'telefono_entrega' => '0991234567',
            'pago_metodo' => 'transferencia',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_no_permite_checkout_con_carrito_vacio(): void
    {
        $user = User::factory()->create(['role' => 'cliente']);
        PerfilCliente::create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('checkout.show'));

        $response->assertRedirect(route('carrito.index'));
        $response->assertSessionHas('error');
    }

    public function test_cliente_ve_confirmacion_de_su_propio_pedido(): void
    {
        [$user] = $this->clienteConCarrito();

        $this->actingAs($user)->post(route('checkout.procesar'), [
            'direccion_envio' => 'Calle 123', 'ciudad_envio' => 'Pichincha',
            'telefono_entrega' => '0991234567',
            'pago_metodo' => 'transferencia',
        ]);

        $pedido = $user->pedidos()->first();
        $response = $this->actingAs($user)->get(route('pedidos.confirmacion', $pedido->numero_pedido));

        $response->assertOk();
        $response->assertSee($pedido->numero_pedido);
    }

    public function test_cliente_no_puede_ver_confirmacion_de_pedido_ajeno(): void
    {
        [$dueño] = $this->clienteConCarrito();
        $this->actingAs($dueño)->post(route('checkout.procesar'), [
            'direccion_envio' => 'Calle 123', 'ciudad_envio' => 'Pichincha',
            'telefono_entrega' => '0991234567',
            'pago_metodo' => 'transferencia',
        ]);
        $pedido = $dueño->pedidos()->first();

        $otroUsuario = User::factory()->create(['role' => 'cliente']);
        $response = $this->actingAs($otroUsuario)->get(route('pedidos.confirmacion', $pedido->numero_pedido));

        $response->assertNotFound();
    }
}
