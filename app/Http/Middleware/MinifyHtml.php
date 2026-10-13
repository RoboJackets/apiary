<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Backstage\MinifyHtml\Middleware\MinifyHtml as BaseMinifyHtml;

class MinifyHtml extends BaseMinifyHtml
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    #[\Override]
    public function handle($request, $next)
    {
        if ($request->is('nova', 'nova/*', 'nova-api', 'nova-api/*')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
