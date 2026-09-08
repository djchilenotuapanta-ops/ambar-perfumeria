<?php
namespace Tests\Feature;

use App\Models\Familia;
use App\Models\Fragancia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoFiltrosTest extends TestCase
{
    use RefreshDatabase;

    private function crearFragancia(array $overrides = []): Fragancia
    {
        $familia = Familia::firstOrCreate(['nombre' => $overrides['familia_nombre'] ?? 'Floral'], ['activo' => true]);
        $fragancia = Fragancia::create(array_merge([
            'nombre' => 'Producto Test', 'casa_perfumista' => 'Casa Test',
            'descripcion' => 'desc', 'genero' => 'unisex', 'activo' => true,
            'familia_id' => $familia->id,
        ], array_diff_key($overrides, ['familia_nombre' => null, 'precio' => null, 'stock' => null])));

        $fragancia->tamanos()->create([
            'tamano' => '100 ml',
            'precio' => $overrides['precio'] ?? 100000,
            'stock'  => $overrides['stock'] ?? 10,
        ]);

        return $fragancia;
    }

    public function test_busqueda_combinada_con_filtro_de_familia_respeta_ambos_criterios(): void
    {
        $floral = Familia::create(['nombre' => 'Floral', 'activo' => true]);
        $oud    = Familia::create(['nombre' => 'Oud', 'activo' => true]);

        $this->crearFragancia([
            'nombre' => 'Rose Oud Especial', 'casa_perfumista' => 'Casa Oud House',
            'familia_id' => $oud->id,
        ]);
        $this->crearFragancia([
            'nombre' => 'Jasmine Pure', 'casa_perfumista' => 'Oud House Paris',
            'familia_id' => $floral->id,
        ]);

        $response = $this->get(route('catalogo', ['familia' => $floral->id, 'buscar' => 'oud']));

        $response->assertOk();
        $response->assertSee('Jasmine Pure');
        $response->assertDontSee('Rose Oud Especial');
    }

    public function test_busqueda_no_devuelve_fragancias_inactivas(): void
    {
        $this->crearFragancia([
            'nombre' => 'Fragancia Descontinuada Oud', 'activo' => false,
        ]);
        $this->crearFragancia([
            'nombre' => 'Fragancia Activa Oud', 'activo' => true,
        ]);

        $response = $this->get(route('catalogo', ['buscar' => 'Oud']));

        $response->assertOk();
        $response->assertSee('Fragancia Activa Oud');
        $response->assertDontSee('Fragancia Descontinuada Oud');
    }

    public function test_filtro_de_genero_funciona_correctamente(): void
    {
        $this->crearFragancia(['nombre' => 'Fragancia Hombre QA', 'genero' => 'hombre']);
        $this->crearFragancia(['nombre' => 'Fragancia Mujer QA', 'genero' => 'mujer']);

        $response = $this->get(route('catalogo', ['genero' => 'hombre']));

        $response->assertOk();
        $response->assertSee('Fragancia Hombre QA');
        $response->assertDontSee('Fragancia Mujer QA');
    }
}
