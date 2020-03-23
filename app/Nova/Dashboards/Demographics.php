<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use App\Nova\Metrics\MajorsBreakdown;
use Illuminate\Http\Request;
use Laravel\Nova\Dashboard;

class Demographics extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(): array
    {
        return [
            (new MajorsBreakdown())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     */
    public static function uriKey(): string
    {
        return 'demographics';
    }
}
