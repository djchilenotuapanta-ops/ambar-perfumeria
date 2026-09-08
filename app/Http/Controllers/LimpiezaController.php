<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class LimpiezaController extends Controller
{
    /**
     * Elimina los tamaños de fragancia que se hayan quedado sin stock (<= 0).
     *
     * Misma lógica que ya se aplicó una vez en la migración
     * 2024_..._eliminar_fragancia_tamanos_sin_stock (ver database/migrations),
     * pero disponible como acción manual para el administrador cuando se
     * acumulan tamaños en 0 que ya no se quieren mostrar.
     */
    public function eliminarTamanosSinStock(): RedirectResponse
    {
        $eliminados = DB::table('fragancia_tamanos')->where('stock', '<=', 0)->delete();

        return back()->with('success', "Se eliminaron {$eliminados} tamaño(s) sin stock.");
    }
}
