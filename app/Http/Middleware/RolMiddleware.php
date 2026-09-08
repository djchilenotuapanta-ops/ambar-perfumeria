<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!auth()->check()) return redirect()->route('login');
        if (!in_array(auth()->user()->role, $roles)) abort(403, 'Sin permiso.');
        return $next($request);
    }
}
