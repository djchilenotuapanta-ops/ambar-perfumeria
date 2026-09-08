<?php
namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRolesTest extends TestCase
{
    use RefreshDatabase;

    private function pedidoPagadoEsteMes(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);
        Pedido::create([
            'user_id' => $cliente->id, 'subtotal' => 500000, 'total' => 500000,
            'pago_estado' => 'pagado', 'created_at' => now(),
        ]);
    }

    public function test_cliente_no_tiene_acceso_al_dashboard_admin(): void
    {
        $this->pedidoPagadoEsteMes();
        $cliente = User::factory()->create(['role' => 'cliente']);

        $response = $this->actingAs($cliente)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    public function test_admin_ve_ventas_del_mes_en_dashboard(): void
    {
        $this->pedidoPagadoEsteMes();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Ventas del mes');
    }
}
