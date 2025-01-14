<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleIsValid
{
    /** http://travel-api.localhost/api/v1/admin/travels/store
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $rolesArray = explode('|', $roles);

        if (! auth()->user()->roles()->whereIn('name', $rolesArray)->exists()) {
            return response()->json([
                'message' => 'Forbidden',
            ], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
