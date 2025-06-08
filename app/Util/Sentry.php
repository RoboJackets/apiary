<?php

declare(strict_types=1);

namespace App\Util;

use Illuminate\Support\Str;
use Sentry\Tracing\SamplingContext;

class Sentry
{
    /**
     * URLs that should be ignored for performance tracing.
     *
     * @phan-read-only
     *
     * @var array<string>
     */
    private static array $ignoreUrls = [
        '/api/v1/info',
        '/attendance/kiosk',
        '/health',
        '/ping',
        '/privacy',
    ];

    public static function tracesSampler(SamplingContext $context): float
    {
        if ($context->getTransactionContext()?->getName() === 'health-check:cache-scheduler-running') {
            return 0;
        }

        if ($context->getTransactionContext()?->getName() === 'horizon:snapshot') {
            return 0;
        }

        if ($context->getParentSampled() === true) {
            return 1;
        }

        $transactionData = $context->getTransactionContext()?->getData();

        if (
            $transactionData !== null &&
            array_key_exists('url', $transactionData) &&
            self::shouldIgnoreUrl($transactionData['url'])
        ) {
            return 0;
        }

        return 1;
    }

    public static function shouldIgnoreUrl(string $url): bool
    {
        return in_array($url, self::$ignoreUrls, true) ||
            Str::startsWith($url, '/horizon/') ||
            Str::startsWith($url, '/apiv3/') ||
            Str::startsWith($url, '/mailbook');
    }
}
