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
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $userRoles = $request->user()?->employee?->roles->pluck('nama_jabatan')->toArray() ?? [];

        if (empty(array_intersect($roles, $userRoles))) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }

}
