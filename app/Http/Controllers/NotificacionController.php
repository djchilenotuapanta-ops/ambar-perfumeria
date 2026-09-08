<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = $user->notifications();

        if ($request->filled('estado')) {
            match ($request->string('estado')->value()) {
                'leidas'    => $query->whereNotNull('read_at'),
                'no_leidas' => $query->whereNull('read_at'),
                default     => null,
            };
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->date('desde'));
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->date('hasta'));
        }

        $notificaciones = $query->latest()->paginate(15)->withQueryString();
        $noLeidas = $user->unreadNotifications()->count();

        $vista = $user->esAdmin() ? 'app.back.notificaciones.index' : 'app.front.notificaciones';

        return view($vista, compact('notificaciones', 'noLeidas'));
    }

    public function marcarLeida(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($id)->markAsRead();
        return back();
    }

    public function marcarNoLeida(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($id)
            ->forceFill(['read_at' => null])->save();
        return back();
    }

    public function marcarTodasLeidas(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Todas las notificaciones se marcaron como leídas.');
    }

    public function eliminar(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();
        return back()->with('success', 'Notificación eliminada.');
    }

    public function eliminarLeidas(Request $request): RedirectResponse
    {
        $borradas = $request->user()->notifications()->whereNotNull('read_at')->delete();

        return back()->with(
            'success',
            $borradas > 0
                ? "Se eliminaron {$borradas} notificación(es) leída(s)."
                : 'No tenías notificaciones leídas para eliminar.'
        );
    }

    public function ir(Request $request, string $id): RedirectResponse
    {
        $notificacion = $request->user()->notifications()->findOrFail($id);

        if (is_null($notificacion->read_at)) {
            $notificacion->markAsRead();
        }

        $url = $notificacion->data['url'] ?? null;

        return $url ? redirect($url) : back();
    }
}
