<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesAccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_accede_a_los_tres_reportes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.reportes.ventas'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reportes.productos'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reportes.clientes'))->assertOk();
    }

    public function test_cliente_no_accede_a_ningun_reporte(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);
        $this->actingAs($cliente)->get(route('admin.reportes.ventas'))->assertForbidden();
        $this->actingAs($cliente)->get(route('admin.reportes.productos'))->assertForbidden();
        $this->actingAs($cliente)->get(route('admin.reportes.clientes'))->assertForbidden();
    }

    public function test_admin_exporta_los_tres_reportes_a_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.reportes.ventas.pdf'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reportes.productos.pdf'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reportes.clientes.pdf'))->assertOk();
    }

    public function test_cliente_no_exporta_ningun_reporte_a_pdf(): void
    {
        $cliente = User::factory()->create(['role' => 'cliente']);
        $this->actingAs($cliente)->get(route('admin.reportes.ventas.pdf'))->assertForbidden();
        $this->actingAs($cliente)->get(route('admin.reportes.productos.pdf'))->assertForbidden();
        $this->actingAs($cliente)->get(route('admin.reportes.clientes.pdf'))->assertForbidden();
    }

    public function test_rechaza_rango_de_fechas_invertido(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(
            route('admin.reportes.ventas', ['desde' => '2026-06-01', 'hasta' => '2026-01-01'])
        );
        $response->assertSessionHasErrors('hasta');
    }
}
