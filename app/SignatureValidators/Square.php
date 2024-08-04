<?php

declare(strict_types=1);

namespace App\SignatureValidators;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;
use Square\Utils\WebhooksHelper;

class Square implements SignatureValidator
{
    /**
     * Verifies a signature on a request from Square.
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        return WebhooksHelper::isValidWebhookEventSignature(
            $request->getContent(),
            $request->header($config->signatureHeaderName),
            $config->signingSecret,
            $request->url()
        );
    }
}
