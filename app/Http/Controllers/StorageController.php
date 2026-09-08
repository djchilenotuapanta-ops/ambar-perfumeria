<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageController extends Controller
{
    public function show($path)
    {
        // Validar que la ruta está en la carpeta permitida (solo envases)
        if (!str_starts_with($path, 'envases/')) {
            abort(403);
        }

        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        // Servir el archivo
        return Storage::disk('public')->download($path);
    }
}
