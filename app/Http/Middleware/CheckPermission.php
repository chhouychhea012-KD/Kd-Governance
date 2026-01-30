<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user is super admin
        if ($user->roles()->where('slug', 'super-admin')->exists()) {
            return $next($request);
        }

        // Check if user has the required permission through their roles
        $hasPermission = $user->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();

        // Also check direct user permissions (if any are assigned directly to user)
        if (!$hasPermission) {
            $hasPermission = $user->permissions()
                ->where('slug', $permission)
                ->exists();
        }

        if (!$hasPermission) {
            if ($request->is('api/*')) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            return inertia('Errors/403');
        }

        return $next($request);
    }
}
