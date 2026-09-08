<?php

namespace Tests\Feature;

use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_guardar_una_reseña_en_una_fragancia(): void
    {
        $familia = Familia::create([
            'nombre' => 'Nicho / Artesanal',
            'descripcion' => 'Familia de prueba',
            'icono' => 'fas fa-flask',
            'activo' => true,
            'es_premium' => true,
        ]);

        $fragancia = Fragancia::create([
            'nombre' => 'Aroma de prueba',
            'slug' => 'aroma-de-prueba',
            'descripcion' => 'Descripción de prueba',
            'casa_perfumista' => 'Casa de prueba',
            'genero' => 'unisex',
            'precio' => 120,
            'stock' => 10,
            'familia_id' => $familia->id,
            'activo' => true,
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('reseñas.store', $fragancia->slug), [
            'calificacion' => 5,
            'comentario' => 'Me encantó la fragancia.',
        ]);

        $response->assertRedirect(route('fragancia.show', $fragancia->slug));

        $this->assertDatabaseHas('resenas', [
            'fragancia_id' => $fragancia->id,
            'user_id' => $user->id,
            'calificacion' => 5,
            'comentario' => 'Me encantó la fragancia.',
        ]);
    }
}
