<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresentacionEnvase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PresentacionEnvaseController extends Controller
{
    public function index()
    {
        $sizes = array_keys(config('comercial.tamanos', []));
        $categories = config('comercial.envases.categorias', []);
        $presentaciones = PresentacionEnvase::get()->groupBy('tamano');

        return view('app.back.envases.index', compact('sizes', 'categories', 'presentaciones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tamano' => ['required', 'string', 'in:' . implode(',', array_keys(config('comercial.tamanos', [])))],
            'categoria' => ['required', 'string', 'in:' . implode(',', array_keys(config('comercial.envases.categorias', [])))],
            'imagen' => ['nullable', 'image', 'max:4096'],
            'costo' => ['required', 'numeric', 'min:0'],
        ]);

        $presentacion = PresentacionEnvase::firstOrNew([
            'tamano' => $validated['tamano'],
            'categoria' => $validated['categoria'],
        ]);

        if ($request->hasFile('imagen')) {
            $presentacion->eliminarImagen();
            $presentacion->imagen = $request->file('imagen')->store('envases', 'public');
        }

        $presentacion->costo = $validated['costo'] ?? 0;
        $presentacion->save();

        return redirect()->route('admin.envases.index')->with('success', 'Imagen de envase guardada correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $presentacion = PresentacionEnvase::findOrFail($id);

        $validated = $request->validate([
            'tamano' => ['required', 'string', 'in:' . implode(',', array_keys(config('comercial.tamanos', [])))],
            'categoria' => ['required', 'string', 'in:' . implode(',', array_keys(config('comercial.envases.categorias', [])))],
            'imagen' => ['nullable', 'image', 'max:4096'],
            'costo' => ['nullable', 'numeric', 'min:0'],
        ]);

        $presentacion->costo = $validated['costo'] ?? $presentacion->costo;

        if ($request->hasFile('imagen')) {
            $presentacion->eliminarImagen();
            $presentacion->imagen = $request->file('imagen')->store('envases', 'public');
        }

        $presentacion->save();

        return redirect()->route('admin.envases.index')->with('success', 'Envase actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $presentacion = PresentacionEnvase::findOrFail($id);
        $presentacion->eliminarImagen();
        $presentacion->delete();

        return redirect()->route('admin.envases.index')->with('success', 'Imagen de envase eliminada correctamente.');
    }
}
