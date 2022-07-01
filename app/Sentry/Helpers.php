<?php

declare(strict_types=1);

namespace App\Sentry;

use Sentry\Tracing\SamplingContext;

class Helpers
{
    /**
     * URLs that should be ignored for performance tracing.
     *
     * @var array<string>
     */
    private static array $ignoreUrls = [
        '/ping',
        '/health',
        '/privacy',
        '/attendance/kiosk',
    ];

    public static function tracesSampler(SamplingContext $context): float
    {
        if (true === $context->getParentSampled()) {
            return 1;
        }

        $transactionData = $context->getTransactionContext()?->getData();

        if (
            null !== $transactionData &&
            array_key_exists('url', $transactionData) &&
            array_key_exists('method', $transactionData) &&
            'GET' === $transactionData['method'] &&
            in_array($transactionData['url'], self::$ignoreUrls, true)
        ) {
            return 0;
        }

        return 1;
    }
}
