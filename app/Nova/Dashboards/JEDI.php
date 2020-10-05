<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use App\Nova\Metrics\AccessActiveMembers;
use App\Nova\Metrics\AccessOverrides;
use App\Nova\Metrics\ActiveMembers;
use App\Nova\Metrics\LinkedGitHubAccounts;
use App\Nova\Metrics\LinkedGoogleAccounts;
use App\Nova\Metrics\PendingGitHubInvitations;
use App\Nova\Metrics\SUMSUsers;
use Illuminate\Http\Request;
use Laravel\Nova\Dashboard;

class JEDI extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(): array
    {
        return [
            (new ActiveMembers())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            })->width('1/2'),
            (new AccessActiveMembers())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            })->width('1/2'),
            (new AccessOverrides())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new SUMSUsers())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new PendingGitHubInvitations())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new LinkedGoogleAccounts())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            })->width('1/2'),
            (new LinkedGitHubAccounts())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            })->width('1/2'),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     */
    public static function uriKey(): string
    {
        return 'jedi';
    }
}
