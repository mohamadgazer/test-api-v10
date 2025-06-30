<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $requiredRole)
    {
        $user = $request->user();
    
        $rolesHierarchy = [
            'viewer' => 1,
            'manager' => 2,
            'super_admin' => 3,
        ];
    
        if (!isset($rolesHierarchy[$user->role]) || $rolesHierarchy[$user->role] < $rolesHierarchy[$requiredRole]) {
            return response()->json(['message' => 'Insufficient privileges'], 403);
        }
    
        return $next($request);
    }
    
}
