<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use HTMLMin\HTMLMin\Http\Middleware\MinifyMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinifyHtml extends MinifyMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('nova', 'nova/*', 'nova-api', 'nova-api/*')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
