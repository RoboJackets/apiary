<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

namespace App\SignatureValidators;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class DocuSign implements SignatureValidator
{
    /**
     * Verifies a signature on a request from DocuSign.
     *
     * Since we're just using the signed routes middleware, this is a no-op.
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        return true;
    }
}
