<?php

declare(strict_types=1);

return [
    'configs' => [
        [
            /*
             * This package supports multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'square',

            /*
             * We expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('SQUARE_WEBHOOK_SIGNATURE_KEY'),

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'X-Square-HmacSha256-Signature',

            /*
             *  This class will verify that the content of the signature header is valid.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => App\SignatureValidators\Square::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * This class determines the response on a valid webhook call.
             */
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,

            /*
             * The classname of the model to be used to store call. The class should be equal
             * or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => App\Jobs\ProcessSquareWebhook::class,
        ],

        [
            /*
             * This package supports multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'postmark-outbound',

            /*
             * We expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('POSTMARK_OUTBOUND_TOKEN'),

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'X-Postmark-Token',

            /*
             *  This class will verify that the content of the signature header is valid.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => App\SignatureValidators\Postmark::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * This class determines the response on a valid webhook call.
             */
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,

            /*
             * The classname of the model to be used to store call. The class should be equal
             * or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => App\Jobs\ProcessPostmarkOutboundWebhook::class,
        ],

        [
            /*
             * This package supports multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'postmark-inbound',

            /*
             * We expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('POSTMARK_INBOUND_TOKEN'),

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'Authorization',

            /*
             *  This class will verify that the content of the signature header is valid.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => App\SignatureValidators\Postmark::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * This class determines the response on a valid webhook call.
             */
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,

            /*
             * The classname of the model to be used to store call. The class should be equal
             * or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => App\Jobs\ProcessPostmarkInboundWebhook::class,
        ],

        [
            /*
             * This package supports multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'docusign',

            /*
             * We expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => null,

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => null,

            /*
             *  This class will verify that the content of the signature header is valid.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => App\SignatureValidators\DocuSign::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * This class determines the response on a valid webhook call.
             */
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,

            /*
             * The classname of the model to be used to store call. The class should be equal
             * or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => App\Jobs\ProcessDocuSignWebhook::class,
        ],
    ],
];
