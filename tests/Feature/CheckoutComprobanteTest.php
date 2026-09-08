<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutComprobanteTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_puede_ver_su_comprobante_de_transferencia(): void
    {
        Storage::fake('public');

        $cliente = User::factory()->create(['role' => 'cliente']);
        $pedido = Pedido::create([
            'user_id' => $cliente->id,
            'numero_pedido' => 'PED-TEST-001',
            'subtotal' => 500,
            'total' => 500,
            'pago_metodo' => 'transferencia',
            'pago_estado' => 'pendiente',
            'estado' => 'pendiente',
            'comprobante_pago' => 'comprobantes/PED-TEST-001/test.jpg',
        ]);

        Storage::disk('public')->put($pedido->comprobante_pago, 'fake-content');

        $response = $this->actingAs($cliente)
            ->get(route('pedidos.ver-comprobante', $pedido->numero_pedido));

        $response->assertOk();
    }

    public function test_admin_puede_ver_comprobante_de_cualquier_pedido(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $cliente = User::factory()->create(['role' => 'cliente']);
        $pedido = Pedido::create([
            'user_id' => $cliente->id,
            'numero_pedido' => 'PED-TEST-002',
            'subtotal' => 500,
            'total' => 500,
            'pago_metodo' => 'transferencia',
            'pago_estado' => 'pendiente',
            'estado' => 'pendiente',
            'comprobante_pago' => 'comprobantes/PED-TEST-002/test.jpg',
        ]);

        Storage::disk('public')->put($pedido->comprobante_pago, 'fake-content');

        $response = $this->actingAs($admin)
            ->get(route('pedidos.ver-comprobante', $pedido->numero_pedido));

        $response->assertOk();
    }
}
