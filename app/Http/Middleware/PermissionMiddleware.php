<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param array<string>|string $permissions_to_check Permissions to authenticate
     */
    public function handle(Request $request, Closure $next, $permissions_to_check)
    {
        if (Auth::guest() && $request->ajax()) {
            return response()->json(['status' => 'error',
                'message' => 'Unauthorized - You must authenticate to perform this action.',
            ], 401);
        }

        if (Auth::guest()) {
            abort(403);
        }

        $permissions = is_array($permissions_to_check) ? $permissions_to_check : explode('|', $permissions_to_check);

        foreach ($permissions as $permission) {
            if ($request->user()->can($permission)) {
                return $next($request);
            }
        }

        if ($request->ajax()) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to perform this action.',
            ], 403);
        }

        abort(403);
    }
}
