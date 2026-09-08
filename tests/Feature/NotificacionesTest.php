<?php
namespace Tests\Feature;

use App\Events\StockBajoDetectado;
use App\Models\Carrito;
use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\Pedido;
use App\Models\PerfilCliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionesTest extends TestCase
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

        return [$user, $fragancia];
    }

    public function test_registro_notifica_a_los_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->post(route('register'), [
            'name' => 'Cliente Nuevo', 'email' => 'nuevo@test.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => \App\Notifications\NuevoUsuarioRegistrado::class,
        ]);
    }

    public function test_checkout_notifica_a_cliente_y_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$user] = $this->clienteConCarrito();

        $this->actingAs($user)->post(route('checkout.procesar'), [
            'direccion_envio' => 'Calle 1', 'ciudad_envio' => 'Pichincha',
            'telefono_entrega' => '0999999999', 'pago_metodo' => 'transferencia',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id, 'type' => \App\Notifications\PedidoConfirmacion::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id, 'type' => \App\Notifications\NuevoPedidoAdmin::class,
        ]);
    }

    public function test_confirmar_pago_notifica_a_cliente_y_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cliente = User::factory()->create(['role' => 'cliente']);
        $pedido = Pedido::create([
            'user_id' => $cliente->id, 'subtotal' => 100000, 'total' => 100000,
            'estado' => 'pendiente', 'pago_estado' => 'pendiente',
        ]);

        $pedido->update(['pago_estado' => 'pagado']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $cliente->id, 'type' => \App\Notifications\PagoConfirmadoCliente::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id, 'type' => \App\Notifications\PagoConfirmadoAdmin::class,
        ]);
    }

    public function test_cancelar_pedido_notifica_a_cliente_y_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cliente = User::factory()->create(['role' => 'cliente']);
        $pedido = Pedido::create([
            'user_id' => $cliente->id, 'subtotal' => 100000, 'total' => 100000,
            'estado' => 'pendiente', 'pago_estado' => 'pendiente',
        ]);

        $pedido->update(['estado' => 'cancelado']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $cliente->id, 'type' => \App\Notifications\PedidoEstadoCambiado::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id, 'type' => \App\Notifications\PedidoCanceladoAdmin::class,
        ]);
    }

    public function test_stock_bajo_notifica_a_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $familia = Familia::create(['nombre' => 'Floral', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Bajo Stock', 'casa_perfumista' => 'Ambar', 'descripcion' => 'd',
            'genero' => 'unisex', 'precio' => 50000, 'stock' => 0, 'activo' => true,
            'familia_id' => $familia->id,
        ]);

        StockBajoDetectado::dispatch($fragancia, 0, 10);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id, 'type' => \App\Notifications\StockBajo::class,
        ]);
    }

    public function test_cliente_puede_ver_y_marcar_sus_notificaciones(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);
        $cliente->notify(new \App\Notifications\ActualizacionCuenta(['correo']));
        $notif = $cliente->notifications()->first();

        $this->actingAs($cliente)->get(route('notificaciones.index'))
            ->assertOk()->assertSee('Datos de cuenta actualizados');

        $this->actingAs($cliente)->patch(route('notificaciones.leida', $notif->id))->assertRedirect();
        $this->assertNotNull($notif->fresh()->read_at);

        $this->actingAs($cliente)->patch(route('notificaciones.noLeida', $notif->id))->assertRedirect();
        $this->assertNull($notif->fresh()->read_at);
    }

    public function test_no_puede_marcar_notificacion_ajena(): void
    {
        $victima = User::factory()->create(['role' => 'cliente']);
        $atacante = User::factory()->create(['role' => 'cliente']);
        $victima->notify(new \App\Notifications\ActualizacionCuenta(['correo']));
        $notif = $victima->notifications()->first();

        $this->actingAs($atacante)->patch(route('notificaciones.leida', $notif->id))->assertNotFound();
        $this->assertNull($notif->fresh()->read_at);
    }

    public function test_admin_puede_ver_sus_notificaciones(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->notify(new \App\Notifications\NuevoUsuarioRegistrado(
            User::factory()->create(['role' => 'cliente'])
        ));

        $this->actingAs($admin)->get(route('admin.notificaciones.index'))
            ->assertOk()->assertSee('Nuevo cliente registrado');
    }

    public function test_ir_marca_como_leida_y_redirige(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);
        $cliente->notify(new \App\Notifications\ActualizacionCuenta(['correo']));
        $notif = $cliente->notifications()->first();

        $this->actingAs($cliente)->get(route('notificaciones.ir', $notif->id))
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($notif->fresh()->read_at);
    }
}
