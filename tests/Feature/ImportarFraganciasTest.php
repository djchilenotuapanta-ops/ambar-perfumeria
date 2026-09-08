<?php
namespace Tests\Feature;

use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportarFraganciasTest extends TestCase
{
    use RefreshDatabase;

    private const ENCABEZADOS = [
        'nombre', 'descripcion', 'casa_perfumista', 'genero', 'familia',
        'notas_salida', 'notas_corazon', 'notas_fondo',
        'precio_por_ml', 'precio_especial_por_ml',
        'stock_100', 'stock_50', 'stock_30', 'activo',
    ];

    /**
     * Arma un CSV en memoria (separado por comas) a partir de filas asociativas
     * que usan las claves de ENCABEZADOS; las que falten quedan vacías.
     */
    private function csvDesdeFilas(array $filas): UploadedFile
    {
        $lineas = [implode(',', self::ENCABEZADOS)];
        foreach ($filas as $fila) {
            $valores = array_map(fn ($col) => (string) ($fila[$col] ?? ''), self::ENCABEZADOS);
            $lineas[] = implode(',', $valores);
        }
        $contenido = "\xEF\xBB\xBF" . implode("\n", $lineas);

        return UploadedFile::fake()->createWithContent('catalogo.csv', $contenido);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_previsualizar_detecta_filas_duplicadas_dentro_del_mismo_archivo(): void
    {
        $admin = $this->admin();

        $filaBase = [
            'nombre' => 'Black Orchid', 'descripcion' => 'desc', 'casa_perfumista' => 'Tom Ford',
            'genero' => 'unisex', 'familia' => 'Oriental',
            'precio_por_ml' => '1.20', 'stock_100' => '10', 'stock_50' => '15', 'stock_30' => '20',
            'activo' => 'si',
        ];

        $archivo = $this->csvDesdeFilas([
            $filaBase,
            $filaBase, // misma fragancia (nombre + casa) repetida en el mismo archivo
        ]);

        $response = $this->actingAs($admin)->post(route('admin.fragancias.importar.previsualizar'), [
            'archivo' => $archivo,
        ]);

        $response->assertOk();
        $response->assertViewHas('validas', function ($validas) {
            return count($validas) === 1;
        });
        $response->assertViewHas('invalidas', function ($invalidas) {
            return count($invalidas) === 1
                && str_contains($invalidas[0]['errores'][0], 'duplicada dentro del archivo');
        });
    }

    public function test_confirmar_no_crea_dos_fragancias_para_filas_duplicadas_del_mismo_archivo(): void
    {
        $admin = $this->admin();

        $filaBase = [
            'nombre' => 'Black Orchid', 'descripcion' => 'desc', 'casa_perfumista' => 'Tom Ford',
            'genero' => 'unisex', 'familia' => 'Oriental',
            'precio_por_ml' => '1.20', 'stock_100' => '10', 'stock_50' => '15', 'stock_30' => '20',
            'activo' => 'si',
        ];

        $archivo = $this->csvDesdeFilas([$filaBase, $filaBase]);

        $preview = $this->actingAs($admin)->post(route('admin.fragancias.importar.previsualizar'), [
            'archivo' => $archivo,
        ]);
        $token = $preview->viewData('token');

        $this->actingAs($admin)->post(route('admin.fragancias.importar.confirmar'), ['token' => $token]);

        $this->assertSame(1, Fragancia::whereRaw('LOWER(nombre) = ?', ['black orchid'])->count());
    }

    public function test_confirmar_reutiliza_familia_existente_sin_importar_mayusculas(): void
    {
        $admin = $this->admin();
        Familia::create(['nombre' => 'Floral', 'activo' => true]);

        $archivo = $this->csvDesdeFilas([[
            'nombre' => 'Chance', 'descripcion' => 'desc', 'casa_perfumista' => 'Chanel',
            'genero' => 'mujer', 'familia' => 'floral', // minúscula, misma familia
            'precio_por_ml' => '1.00', 'stock_100' => '5', 'stock_50' => '5', 'stock_30' => '5',
            'activo' => 'si',
        ]]);

        $preview = $this->actingAs($admin)->post(route('admin.fragancias.importar.previsualizar'), [
            'archivo' => $archivo,
        ]);
        $token = $preview->viewData('token');

        $this->actingAs($admin)->post(route('admin.fragancias.importar.confirmar'), ['token' => $token]);

        $this->assertSame(1, Familia::whereRaw('LOWER(nombre) = ?', ['floral'])->count());
    }

    public function test_flujo_completo_crea_fragancia_nueva_con_sus_tamanos(): void
    {
        $admin = $this->admin();

        $archivo = $this->csvDesdeFilas([[
            'nombre' => 'Sauvage', 'descripcion' => 'desc', 'casa_perfumista' => 'Dior',
            'genero' => 'hombre', 'familia' => 'Fresco',
            'precio_por_ml' => '1.00', 'stock_100' => '10', 'stock_50' => '20', 'stock_30' => '30',
            'activo' => 'si',
        ]]);

        $preview = $this->actingAs($admin)->post(route('admin.fragancias.importar.previsualizar'), [
            'archivo' => $archivo,
        ]);
        $token = $preview->viewData('token');

        $response = $this->actingAs($admin)->post(route('admin.fragancias.importar.confirmar'), ['token' => $token]);

        $response->assertRedirect(route('admin.fragancias.index'));
        $response->assertSessionHas('success');

        $fragancia = Fragancia::where('nombre', 'Sauvage')->first();
        $this->assertNotNull($fragancia);
        $this->assertSame(3, $fragancia->tamanos()->count());
        $this->assertSame(10, $fragancia->tamanos()->where('tamano', '100 ml')->value('stock'));
    }

    public function test_confirmar_actualiza_fragancia_existente_y_reemplaza_su_stock(): void
    {
        $admin = $this->admin();
        $familia = Familia::create(['nombre' => 'Fresco', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Sauvage', 'casa_perfumista' => 'Dior', 'descripcion' => 'vieja',
            'genero' => 'hombre', 'familia_id' => $familia->id,
            'precio_por_ml' => 1.00, 'activo' => true,
        ]);
        $fragancia->tamanos()->create(['tamano' => '100 ml', 'precio' => 100, 'stock' => 999]);

        $archivo = $this->csvDesdeFilas([[
            'nombre' => 'Sauvage', 'descripcion' => 'nueva descripcion', 'casa_perfumista' => 'Dior',
            'genero' => 'hombre', 'familia' => 'Fresco',
            'precio_por_ml' => '1.00', 'stock_100' => '2', 'stock_50' => '2', 'stock_30' => '2',
            'activo' => 'si',
        ]]);

        $preview = $this->actingAs($admin)->post(route('admin.fragancias.importar.previsualizar'), [
            'archivo' => $archivo,
        ]);
        $token = $preview->viewData('token');

        $this->actingAs($admin)->post(route('admin.fragancias.importar.confirmar'), ['token' => $token]);

        $this->assertSame(1, Fragancia::whereRaw('LOWER(nombre) = ?', ['sauvage'])->count());
        $fragancia->refresh();
        $this->assertSame('nueva descripcion', $fragancia->descripcion);
        $this->assertSame(2, $fragancia->tamanos()->where('tamano', '100 ml')->value('stock'));
    }

    public function test_confirmar_notifica_a_otros_admins_pero_no_a_quien_importo(): void
    {
        $admin = $this->admin();
        $otroAdmin = $this->admin();

        $archivo = $this->csvDesdeFilas([[
            'nombre' => 'Sauvage', 'descripcion' => 'desc', 'casa_perfumista' => 'Dior',
            'genero' => 'hombre', 'familia' => 'Fresco',
            'precio_por_ml' => '1.00', 'stock_100' => '10', 'stock_50' => '20', 'stock_30' => '30',
            'activo' => 'si',
        ]]);

        $preview = $this->actingAs($admin)->post(route('admin.fragancias.importar.previsualizar'), [
            'archivo' => $archivo,
        ]);
        $token = $preview->viewData('token');

        $this->actingAs($admin)->post(route('admin.fragancias.importar.confirmar'), ['token' => $token]);

        $this->assertSame(0, $admin->fresh()->notifications()->count());
        $this->assertSame(1, $otroAdmin->fresh()->notifications()->count());
        $this->assertStringContainsString($admin->name, $otroAdmin->fresh()->notifications()->first()->data['mensaje']);
    }

    public function test_previsualizar_normaliza_genero_con_mayusculas_y_espacios(): void
    {
        $admin = $this->admin();

        $archivo = $this->csvDesdeFilas([[
            'nombre' => 'Sauvage', 'descripcion' => 'desc', 'casa_perfumista' => 'Dior',
            'genero' => ' HOMBRE ', 'familia' => 'Fresco',
            'precio_por_ml' => '1.00', 'stock_100' => '10', 'stock_50' => '20', 'stock_30' => '30',
            'activo' => 'si',
        ]]);

        $response = $this->actingAs($admin)->post(route('admin.fragancias.importar.previsualizar'), [
            'archivo' => $archivo,
        ]);

        $response->assertViewHas('validas', fn ($validas) => count($validas) === 1);
        $response->assertViewHas('invalidas', fn ($invalidas) => count($invalidas) === 0);
    }

    public function test_previsualizar_muestra_el_diff_de_una_fila_a_actualizar(): void
    {
        $admin = $this->admin();
        $familia = Familia::create(['nombre' => 'Fresco', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Sauvage', 'casa_perfumista' => 'Dior', 'descripcion' => 'vieja',
            'genero' => 'hombre', 'familia_id' => $familia->id,
            'precio_por_ml' => 1.00, 'activo' => true,
        ]);
        $fragancia->tamanos()->create(['tamano' => '100 ml', 'precio' => 100, 'stock' => 999]);

        $archivo = $this->csvDesdeFilas([[
            'nombre' => 'Sauvage', 'descripcion' => 'nueva descripcion', 'casa_perfumista' => 'Dior',
            'genero' => 'hombre', 'familia' => 'Fresco',
            'precio_por_ml' => '1.00', 'stock_100' => '2', 'stock_50' => '2', 'stock_30' => '2',
            'activo' => 'si',
        ]]);

        $response = $this->actingAs($admin)->post(route('admin.fragancias.importar.previsualizar'), [
            'archivo' => $archivo,
        ]);

        $response->assertViewHas('validas', function ($validas) {
            $cambios = collect($validas[0]['cambios'])->keyBy('campo');
            return $cambios->has('Descripción')
                && $cambios->has('Stock 100 ml')
                && $cambios['Stock 100 ml']['antes'] === 999
                && $cambios['Stock 100 ml']['despues'] === 2;
        });
    }

    public function test_previsualizar_no_marca_como_cambio_un_precio_con_igual_valor_pero_distinto_formato(): void
    {
        $admin = $this->admin();
        $familia = Familia::create(['nombre' => 'Fresco', 'activo' => true]);
        $fragancia = Fragancia::create([
            'nombre' => 'Sauvage', 'casa_perfumista' => 'Dior', 'descripcion' => 'desc',
            'genero' => 'hombre', 'familia_id' => $familia->id,
            'precio_por_ml' => 1.00, 'activo' => true,
        ]);
        $fragancia->tamanos()->create(['tamano' => '100 ml', 'precio' => 100, 'stock' => 10]);

        // El CSV trae "1" (sin decimales) para el mismo precio que ya tiene la BD (1.00).
        $archivo = $this->csvDesdeFilas([[
            'nombre' => 'Sauvage', 'descripcion' => 'desc', 'casa_perfumista' => 'Dior',
            'genero' => 'hombre', 'familia' => 'Fresco',
            'precio_por_ml' => '1', 'stock_100' => '10', 'stock_50' => '10', 'stock_30' => '10',
            'activo' => 'si',
        ]]);

        $response = $this->actingAs($admin)->post(route('admin.fragancias.importar.previsualizar'), [
            'archivo' => $archivo,
        ]);

        $response->assertViewHas('validas', function ($validas) {
            return collect($validas[0]['cambios'])->firstWhere('campo', 'Precio/ml') === null;
        });
    }
}
