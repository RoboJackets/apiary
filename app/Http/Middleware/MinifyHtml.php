<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use HTMLMin\HTMLMin\Http\Middleware\MinifyMiddleware;

class MinifyHtml extends MinifyMiddleware
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    #[\Override]
    public function handle($request, Closure $next)
    {
        if ($request->is('nova', 'nova/*', 'nova-api', 'nova-api/*')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
