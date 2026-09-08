<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        $buscar = trim((string) request('buscar', ''));

        $usuarios = User::query()
            ->with('perfil')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($sub) use ($buscar) {
                    $sub->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('role', 'like', "%{$buscar}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('app.back.usuarios.index', compact('usuarios', 'buscar'));
    }

    public function edit(string $id)
    {
        $usuario = User::findOrFail($id);
        return view('app.back.usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, string $id)
    {
        $usuario = User::findOrFail($id);
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $id,
            'role'     => ['required', Rule::in(UserRole::values())],
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Igual que con la autoeliminación: un admin no puede cambiar su
        // propio rol desde este panel. Sin este resguardo podría
        // autodegradarse a "cliente" por error y, si era el único admin,
        // nadie más podría revertirlo.
        if ((int) $id === (int) Auth::id() && $data['role'] !== $usuario->role) {
            return redirect()->route('admin.usuarios.index')
                             ->with('error', 'No puedes cambiar tu propio rol.');
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')
                         ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        if ((int) $id === (int) Auth::id()) {
            return redirect()->route('admin.usuarios.index')
                             ->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        User::findOrFail($id)->delete();
        return redirect()->route('admin.usuarios.index')
                         ->with('success', 'Usuario eliminado correctamente.');
    }
}
