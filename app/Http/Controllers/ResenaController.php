<?php

namespace App\Http\Controllers;

use App\Models\Fragancia;
use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResenaController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $fragancia = Fragancia::where('slug', $slug)->where('activo', true)->firstOrFail();

        $data = $request->validate([
            'calificacion' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'calificacion.required' => 'Selecciona la calificación.',
            'calificacion.min' => 'La calificación mínima es 1 estrella.',
            'calificacion.max' => 'La calificación máxima es 5 estrellas.',
            'comentario.required' => 'Escribe tu comentario sobre la fragancia.',
            'comentario.min' => 'El comentario debe tener al menos 5 caracteres.',
            'comentario.max' => 'El comentario no puede superar 500 caracteres.',
        ]);

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $resena = Resena::updateOrCreate(
            [
                'fragancia_id' => $fragancia->id,
                'user_id' => $user->id,
            ],
            [
                'calificacion' => (int) $data['calificacion'],
                'comentario' => trim($data['comentario']),
            ]
        );

        return redirect()->route('fragancia.show', $fragancia->slug)
            ->with('success', 'Gracias por compartir tu reseña.');
    }
}
