<?php

declare(strict_types=1);

namespace App\SignatureValidators;

use Exception;
use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class Square implements SignatureValidator
{
    /**
     * Verifies a signature on a request from Square.
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $sentSignature = $request->header($config->signatureHeaderName);
        $secret = $config->signingSecret;
        $payload = $request->getContent();

        if (! is_string($sentSignature)) {
            throw new Exception('Header is not a string, possibly missing');
        }

        if (! is_string($payload)) {
            throw new Exception('Payload is not a string');
        }

        $calculatedSignature = base64_encode(hash_hmac('sha1', $request->url().$payload, $secret, true));

        return sha1($calculatedSignature) === sha1($sentSignature);
    }
}
