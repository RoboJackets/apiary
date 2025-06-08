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
    #[\Override]
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        return WebhooksHelper::verifySignature(
            requestBody: $request->getContent(),
            signatureHeader: $request->header($config->signatureHeaderName),
            signatureKey: $config->signingSecret,
            notificationUrl: $request->url()
        );
    }
}
