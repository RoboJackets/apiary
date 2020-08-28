<?php

declare(strict_types=1);

namespace Laravel\Nova\Http\Controllers;

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.Found
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

function filemtime(string $path): int
{
    return time();
}
