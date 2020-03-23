<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use App\Nova\Metrics\ClassStandingsBreakdown;
use App\Nova\Metrics\MajorsBreakdown;
use App\Nova\Metrics\SchoolsBreakdown;
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
            (new SchoolsBreakdown())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new ClassStandingsBreakdown())->canSee(static function (Request $request): bool {
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

    /**
     * Get the displayable name of the dashboard.
     *
     * @return string
     */
    public static function label()
    {
        return 'Demographics';
    }
}
