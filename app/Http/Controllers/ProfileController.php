<?php
namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Notifications\ActualizacionCuenta;
use App\Services\NotificacionSegura;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        $etiquetas = ['name' => 'nombre', 'email' => 'correo'];
        $camposCambiados = array_values(array_intersect_key($etiquetas, $user->getDirty()));

        if ($request->hasFile('foto')) {
            $perfil = $user->perfil()->firstOrCreate([]);
            if (!empty($perfil->foto) && Storage::disk('public')->exists($perfil->foto)) {
                Storage::disk('public')->delete($perfil->foto);
            }
            $path = $request->file('foto')->store('fotos-perfil', 'public');
            $perfil->foto = $path;
            $perfil->save();
            $camposCambiados[] = 'foto de perfil';
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        if (count($camposCambiados) > 0) {
            NotificacionSegura::enviar($user, new ActualizacionCuenta($camposCambiados));
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', ['password' => ['required', 'current_password']]);
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Redirect::to('/');
    }
}
