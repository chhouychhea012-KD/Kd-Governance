<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user is super admin
        if ($user->roles()->where('slug', 'super-admin')->exists()) {
            return $next($request);
        }

        // Check if user has any of the required roles
        $hasRole = $user->roles()
            ->whereIn('slug', $roles)
            ->exists();

        if (!$hasRole) {
            return inertia('Errors/403');
        }

        return $next($request);
    }
}