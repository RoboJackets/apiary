<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::guest() && $request->ajax()) {
            return response()->json(['status' => 'error',
                'message' => 'Unauthorized - You must authenticate to perform this action.', ], 401);
        } elseif (Auth::guest()) {
            abort(403);
        }

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        foreach ($permissions as $permission) {
            if (Auth::user()->can($permission)) {
                return $next($request);
            }
        }

        if ($request->ajax()) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to perform this action.', ], 403);
        } else {
            abort(403);
        }
    }
}
