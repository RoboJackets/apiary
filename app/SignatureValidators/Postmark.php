<?php

declare(strict_types=1);

namespace App\SignatureValidators;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class Postmark implements SignatureValidator
{
    /**
     * Verifies a signature on a request from Postmark.
     */
    #[\Override]
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $sentToken = $request->header($config->signatureHeaderName);
        $secret = $config->signingSecret;

        if (! is_string($sentToken)) {
            return false;
        }

        return $sentToken === $secret;
    }
}
