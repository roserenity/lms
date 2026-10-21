<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class InstructorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->guard('api')->user();
        $allowedRoles = [User::ROLE_ADMIN, User::ROLE_INSTRUCTOR]; // Add higher roles here if needed

        if (!$user || !in_array($user->role, $allowedRoles, true)) {
            return response()->json([
                'message' => 'Unauthorized access.',
                'auth_role_required' => implode(', ', $allowedRoles)
            ], 403);
        }

        return $next($request);
    }
}
