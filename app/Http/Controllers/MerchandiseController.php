<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments

namespace App\Http\Controllers;

use App\Http\Requests\DistributeMerchandiseRequest;
use App\Http\Resources\DuesTransactionMerchandise as DuesTransactionMerchandiseResource;
use App\Http\Resources\Merchandise as MerchandiseResource;
use App\Http\Resources\User as UserResource;
use App\Models\DuesTransactionMerchandise;
use App\Models\Merchandise;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;

class MerchandiseController extends Controller
{
    private const NOT_DISTRIBUTABLE = 'This item cannot be distributed.';

    private const NO_DTM = 'This person doesn\'t have a paid transaction for this item.';

    private const ALREADY_DISTRIBUTED = 'This item was already distributed to this person.';

    public function __construct()
    {
        $this->middleware('permission:read-merchandise|distribute-swag', ['only' => ['index']]);
        $this->middleware('permission:distribute-swag', ['only' => ['getDistribution', 'distribute']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            [
                'status' => 'success',
                'merchandise' => MerchandiseResource::collection(
                    Merchandise::whereDistributable(true)
                        ->orderByDesc('fiscal_year_id')
                        ->get()
                ),
            ]
        );
    }

    public function getDistribution(Merchandise $merchandise, User $user): JsonResponse
    {
        if (! $merchandise->distributable) {
            return response()->json(
                data: [
                    'status' => 'error',
                    'message' => self::NOT_DISTRIBUTABLE,
                ],
                status: 400
            );
        }

        try {
            $dtm = self::getDuesTransactionMerchandise($merchandise, $user);
        } catch (ModelNotFoundException) {
            return response()->json(
                data: [
                    'status' => 'error',
                    'message' => self::NO_DTM,
                ],
                status: 404
            );
        }

        return response()->json(
            [
                'status' => 'success',
                'merchandise' => MerchandiseResource::make($merchandise),
                'user' => UserResource::make($user),
                'distribution' => DuesTransactionMerchandiseResource::make($dtm, false),
                'can_distribute' => $dtm->provided_at === null,
            ]
        );
    }

    public function distribute(
        DistributeMerchandiseRequest $request,
        Merchandise $merchandise,
        User $user
    ): JsonResponse {
        if (! $merchandise->distributable) {
            return response()->json(
                data: [
                    'status' => 'error',
                    'message' => self::NOT_DISTRIBUTABLE,
                ],
                status: 400
            );
        }

        try {
            $dtm = self::getDuesTransactionMerchandise($merchandise, $user);
        } catch (ModelNotFoundException) {
            return response()->json(
                data: [
                    'status' => 'error',
                    'message' => self::NO_DTM,
                ],
                status: 404
            );
        }

        if ($dtm->provided_at !== null) {
            return response()->json(
                data: [
                    'status' => 'error',
                    'message' => self::ALREADY_DISTRIBUTED,
                ],
                status: 409
            );
        }

        $dtm->provided_at = Carbon::now();
        $dtm->provided_by = $request->user()->id;
        $dtm->provided_via = $request->provided_via;
        $dtm->save();

        return response()->json(
            [
                'status' => 'success',
                'merchandise' => MerchandiseResource::make($merchandise),
                'user' => UserResource::make($user),
                'distribution' => DuesTransactionMerchandiseResource::make($dtm, false),
                'can_distribute' => $dtm->provided_at === null,
            ]
        );
    }

    private static function getDuesTransactionMerchandise(
        Merchandise $merchandise,
        User $user
    ): DuesTransactionMerchandise {
        return DuesTransactionMerchandise::select(
            'dues_transaction_merchandise.id',
            'dues_transaction_merchandise.provided_at',
            'dues_transaction_merchandise.provided_by',
            'dues_transaction_merchandise.provided_via',
            'dues_transaction_merchandise.merchandise_id'
        )
            ->whereHas('transaction', static function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)
                    ->paid();
            })
            ->where('merchandise_id', $merchandise->id)
            ->sole();
    }
}
