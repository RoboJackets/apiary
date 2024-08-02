<?php

declare(strict_types=1);

namespace App\Nova\Actions\Payments;

use App\Models\DuesTransaction;
use App\Models\Payable;
use App\Models\TravelAssignment;
use Exception;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Registers Nova actions for offline payment methods.
 *
 * @phan-suppress PhanCompatibleTraitConstant
 * @phan-suppress PhanUnreferencedPrivateClassConstant
 */
trait RecordPaymentActions
{
    private const OFFLINE_PAYMENT_METHODS = [
        RecordCashPayment::METHOD => RecordCashPayment::class,
        RecordCheckPayment::METHOD => RecordCheckPayment::class,
        ApplyWaiver::METHOD => ApplyWaiver::class,
    ];

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        $resourceType = $request->resource;
        $resourceId = $request->resourceId ?? $request->resources;
        $user = $request->user();

        if ($resourceType === null || $resourceId === null || $user === null || is_array($resourceId)) {
            return [];
        }

        if ($resourceType === \App\Nova\TravelAssignment::uriKey()) {
            $payable = TravelAssignment::find($resourceId);
        } elseif ($resourceType === \App\Nova\DuesTransaction::uriKey()) {
            $payable = DuesTransaction::find($resourceId);

            if ($payable === null || ! $payable->package->is_active) {
                return [];
            }
        } else {
            throw new Exception('Unexpected resource type '.$resourceType);
        }

        if ($payable === null || $payable->is_paid) {
            return [];
        }

        if ($resourceType === \App\Nova\TravelAssignment::uriKey()) {
            if ($payable->travel->status === 'draft') {
                return [];
            }
        }

        $actions = [];

        foreach (self::OFFLINE_PAYMENT_METHODS as $method => $class) {
            if ($user->can('create-payments-'.$method)) {
                if ($payable->user->id === $user->id) {
                    $actions[] = self::selfTransactionNotAllowed($method, $resourceType);
                } elseif (! $payable->user->signed_latest_agreement) {
                    $actions[] = self::memberHasNotSignedAgreement($method);
                } else {
                    $actions[] = call_user_func([$class, 'make'])
                        ->canRun(
                            static fn (
                                NovaRequest $request,
                                Payable $payable
                            ): bool => $request->user()->can('create-payments-'.$method) &&
                                $request->user()->id !== $payable->user->id &&
                                $payable->user->signed_latest_agreement
                        );
                }
            }
        }

        return $actions;
    }

    private static function selfTransactionNotAllowed(string $method, string $resourceType): Action
    {
        return Action::danger(
            call_user_func([self::OFFLINE_PAYMENT_METHODS[$method], 'make'])->name(),
            'You may not record a '.$method.' payment for your own '.
            Str::singular(str_replace('-', ' ', $resourceType)).'.'
        )
            ->withoutConfirmation()
            ->canRun(static fn (): bool => true);
    }

    private static function memberHasNotSignedAgreement(string $method): Action
    {
        return Action::danger(
            call_user_func([self::OFFLINE_PAYMENT_METHODS[$method], 'make'])->name(),
            'This member has not signed the latest membership agreement.'
        )
            ->withoutConfirmation()
            ->canRun(static fn (): bool => true);
    }
}
