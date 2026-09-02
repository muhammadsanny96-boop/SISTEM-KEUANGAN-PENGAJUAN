<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('error', 'Akses dialihkan ke dashboard Admin.');
            }

            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Anda tidak memiliki izin untuk halaman tersebut.');
        }

        return $next($request);
    }
}
