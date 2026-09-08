<?php
namespace Tests\Feature;

use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\FraganciaTamano;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarritoStockTest extends TestCase
{
    use RefreshDatabase;

    private function tamanoConStock(int $stock, bool $activo = true): FraganciaTamano
    {
        $familia = Familia::create(['nombre' => 'Floral', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Test Parfum', 'casa_perfumista' => 'Ambar Maison',
            'descripcion' => 'desc', 'genero' => 'unisex', 'activo' => $activo,
            'familia_id' => $familia->id,
        ]);

        return $fragancia->tamanos()->create([
            'tamano' => '100 ml', 'precio' => 100000, 'stock' => $stock,
        ]);
    }

    public function test_no_permite_agregar_mas_cantidad_que_el_stock_disponible(): void
    {

        $user = User::factory()->create(['role' => 'cliente']);
        $tamano = $this->tamanoConStock(stock: 3);

        $response = $this->actingAs($user)->post(route('carrito.agregar'), [
            'fragancia_tamano_id' => $tamano->id,
            'cantidad'            => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('carrito', 0);
    }

    public function test_permite_agregar_cantidad_dentro_del_stock(): void
    {

        $user = User::factory()->create(['role' => 'cliente']);
        $tamano = $this->tamanoConStock(stock: 10);

        $response = $this->actingAs($user)->post(route('carrito.agregar'), [
            'fragancia_tamano_id' => $tamano->id,
            'cantidad'            => 4,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('carrito', [
            'user_id' => $user->id, 'fragancia_tamano_id' => $tamano->id, 'cantidad' => 4,
        ]);
    }

    public function test_no_permite_sobrepasar_stock_al_agregar_en_dos_pasos(): void
    {

        $user = User::factory()->create(['role' => 'cliente']);
        $tamano = $this->tamanoConStock(stock: 5);

        $this->actingAs($user)->post(route('carrito.agregar'), [
            'fragancia_tamano_id' => $tamano->id, 'cantidad' => 4,
        ]);
        $response = $this->actingAs($user)->post(route('carrito.agregar'), [
            'fragancia_tamano_id' => $tamano->id, 'cantidad' => 3,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('carrito', ['fragancia_tamano_id' => $tamano->id, 'cantidad' => 4]);
    }

    public function test_no_permite_agregar_fragancia_inactiva(): void
    {

        $user = User::factory()->create(['role' => 'cliente']);
        $tamano = $this->tamanoConStock(stock: 10, activo: false);

        $response = $this->actingAs($user)->post(route('carrito.agregar'), [
            'fragancia_tamano_id' => $tamano->id, 'cantidad' => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('carrito', 0);
    }

    public function test_notifica_al_admin_y_al_cliente_cuando_se_solicita_mas_stock_del_disponible(): void
    {

        $user = User::factory()->create(['role' => 'cliente']);

        $admin = User::factory()->create(['role' => 'admin']);
        $tamano = $this->tamanoConStock(stock: 3);

        $response = $this->actingAs($user)->post(route('carrito.agregar'), [
            'fragancia_tamano_id' => $tamano->id,
            'cantidad'            => 5,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('carrito', 0);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => 'App\\Notifications\\SolicitudStock',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => 'App\\Notifications\\SolicitudStock',
        ]);
    }

    public function test_vaciar_carrito_redirige_sin_error(): void
    {

        $user = User::factory()->create(['role' => 'cliente']);
        $tamano = $this->tamanoConStock(stock: 10);
        $this->actingAs($user)->post(route('carrito.agregar'), [
            'fragancia_tamano_id' => $tamano->id, 'cantidad' => 1,
        ]);

        $response = $this->actingAs($user)->delete(route('carrito.vaciar'));

        $response->assertRedirect(route('carrito.index'));
        $this->assertDatabaseCount('carrito', 0);
    }

    public function test_vaciar_carrito_por_ajax_retorna_json(): void
    {
        $user = User::factory()->create(['role' => 'cliente']);
        $tamano = $this->tamanoConStock(stock: 10);
        $this->actingAs($user)->post(route('carrito.agregar'), [
            'fragancia_tamano_id' => $tamano->id,
            'cantidad' => 2,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('carrito.vaciar'));

        $response->assertOk()
            ->assertJson(['ok' => true, 'message' => 'Carrito vaciado.']);
        $this->assertDatabaseCount('carrito', 0);
    }

    public function test_el_carrito_muestra_importes_con_decimales_exactos(): void
    {
        $user = User::factory()->create(['role' => 'cliente']);

        $familia = Familia::create(['nombre' => 'Floral', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Precio Exacto',
            'casa_perfumista' => 'Ambar Maison',
            'descripcion' => 'desc',
            'genero' => 'unisex',
            'activo' => true,
            'familia_id' => $familia->id,
        ]);

        $tamano = $fragancia->tamanos()->create([
            'tamano' => '50 ml',
            'precio' => 46.50,
            'stock' => 5,
        ]);

        $this->actingAs($user)->post(route('carrito.agregar'), [
            'fragancia_tamano_id' => $tamano->id,
            'cantidad' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('carrito.index'));

        // Por defecto el IVA viene incluido en el precio guardado (ver
        // config/comercial.php: iva.incluido_en_precio = true, práctica
        // habitual en Ecuador), así que $46,50 ya es el precio "Con IVA"
        // y se desglosa hacia abajo, no se suma encima.
        $response->assertOk();
        $response->assertSee('$46,50');
        $response->assertSee('Sin IVA: $40,43');
        $response->assertSee('IVA (15,00%): $6,07');
        $response->assertSee('Con IVA: $46,50');
    }
}
