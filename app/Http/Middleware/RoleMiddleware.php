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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle($request, Closure $next, ...$roles)
{
    $user = $request->user();
    if (!$user) {
        return redirect()->route('login');
    }

    $userRoles = $user->employee?->roles->pluck('nama_jabatan')->toArray() ?? [];

    // ✅ kalau route minta "other"
    if (in_array('other', $roles) && empty(array_intersect($userRoles, ['hr', 'kepala_divisi']))) {
        return $next($request);
    }

    // ✅ kalau user punya salah satu role yang diminta
    if (!empty(array_intersect($roles, $userRoles))) {
        return $next($request);
    }

    abort(403, 'Unauthorized');
}
}
