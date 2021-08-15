<?php

declare(strict_types=1);

namespace App\Nova\Traits;

use App\Nova\Metrics\MembersForOneObject;
use App\Nova\Metrics\PaymentMethodBreakdown;
use App\Nova\Metrics\TotalCollections;
use Illuminate\Http\Request;

trait DuesPackageCards
{
    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [
            (new MembersForOneObject())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-payments');
                }),
            (new TotalCollections())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-payments');
                }),
            (new PaymentMethodBreakdown())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-payments');
                }),
        ];
    }
}
