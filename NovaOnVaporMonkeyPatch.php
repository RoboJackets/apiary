<?php

declare(strict_types=1);

namespace Laravel\Nova\Http\Controllers;

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.Found
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/**
 * Monkey patch for Nova because it uses this for URLs.
 *
 * @param string $path the path (really URL) to the file Nova is trying to serve
 *
 * @return int the current unix timestamp
 *
 * @phan-suppress PhanUnreferencedFunction
 * @phan-suppress PhanUnusedGlobalFunctionParameter
 */
function filemtime(string $path): int
{
    return time();
}
