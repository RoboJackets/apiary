<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

namespace App\Http\Middleware;

use Closure;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\State\Scope;

class Sentry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function handle($request, Closure $next)
    {
        if (app()->bound('sentry')) {
            \Sentry\configureScope(static function (Scope $scope): void {
                if (auth()->check()) {
                    $scope->setUser([
                        'id' => auth()->user()->id,
                        'username' => auth()->user()->uid,
                    ]);
                }

                $scope->addEventProcessor(static function (Event $event, EventHint $hint): Event {
                    $request = $event->getRequest();

                    if (array_key_exists('data', $request) && array_key_exists('refresh_token', $request['data'])) {
                        $request['data']['refresh_token'] = '[redacted]';
                    }

                    if (array_key_exists('headers', $request)) {
                        $request['headers'] = collect($request['headers'])->map(
                            static function (array $values, string $header): array {
                                if (
                                    0 === strcasecmp($header, 'X-Xsrf-Token') ||
                                    0 === strcasecmp($header, 'X-Csrf-Token')
                                ) {
                                    return ['[redacted]'];
                                }

                                return $values;
                            }
                        );
                    }

                    $event->setRequest($request);

                    return $event;
                });
            });
        }

        return $next($request);
    }
}
