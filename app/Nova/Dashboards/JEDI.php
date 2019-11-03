<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;
use Illuminate\Http\Request;
use App\Nova\Metrics\SUMSUsers;
use App\Nova\Metrics\AccessOverrides;
use App\Nova\Metrics\LinkedGitHubAccounts;
use App\Nova\Metrics\LinkedGoogleAccounts;
use App\Nova\Metrics\PendingGitHubInvitations;

class JEDI extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            (new AccessOverrides)->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new SUMSUsers)->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new LinkedGoogleAccounts)->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new LinkedGitHubAccounts)->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new PendingGitHubInvitations)->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
        ];
    }
}
