<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolAccesoAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_no_accede_al_panel_admin(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);
        $response = $this->actingAs($cliente)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    public function test_admin_accede_a_fragancias_pedidos_y_usuarios(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.fragancias.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.pedidos.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.usuarios.index'))->assertOk();
    }

    public function test_usuario_no_autenticado_es_redirigido_a_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }
}
