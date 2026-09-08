<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Familia;
use Illuminate\Http\Request;

class FamiliaController extends Controller
{
    public function index()
    {
        $familias = Familia::withCount('fragancias')->latest()->get();
        return view('app.back.familias.index', compact('familias'));
    }

    public function create()
    {
        return view('app.back.familias.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'icono'       => 'nullable|string|max:100',
            'activo'      => 'required|boolean',
            'es_premium'  => 'required|boolean',
        ]);

        Familia::create($data);
        return redirect()->route('admin.familias.index')
                         ->with('success', 'Familia olfativa creada correctamente.');
    }

    public function edit(string $id)
    {
        $familia = Familia::findOrFail($id);
        return view('app.back.familias.edit', compact('familia'));
    }

    public function update(Request $request, string $id)
    {
        $familia = Familia::findOrFail($id);
        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'icono'       => 'nullable|string|max:100',
            'activo'      => 'required|boolean',
            'es_premium'  => 'required|boolean',
        ]);
        $familia->update($data);
        return redirect()->route('admin.familias.index')
                         ->with('success', 'Familia olfativa actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $familia = Familia::findOrFail($id);
        if ($familia->fragancias()->count() > 0) {
            return redirect()->route('admin.familias.index')
                             ->with('error', 'No se puede eliminar: la familia tiene fragancias asociadas.');
        }
        $familia->delete();
        return redirect()->route('admin.familias.index')
                         ->with('success', 'Familia olfativa eliminada correctamente.');
    }
}
