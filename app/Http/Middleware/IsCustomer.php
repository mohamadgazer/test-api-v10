<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'customer') {
            return response()->json(['message' => 'Access denied. Customers only.'], 403);
        }

        return $next($request);
    }
}