<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistroTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_crea_perfil_cliente_automaticamente(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Cliente Prueba',
            'email' => 'prueba@correo.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'prueba@correo.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('cliente', $user->role);
        $this->assertNotNull($user->perfil);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_usuario_staff_no_requiere_perfil_cliente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertNull($admin->perfil);
        $this->assertTrue($admin->tieneAccesoAdmin());
    }
}
