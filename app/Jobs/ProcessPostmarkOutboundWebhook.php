<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\Signature;
use App\Models\SponsorUser;
use App\Models\User;
use Laravel\Passport\Token;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessPostmarkOutboundWebhook extends ProcessWebhookJob
{
    /**
     * The queue this job will run on. This is fairly arbitrary since it only touches the local DB.
     *
     * @var string
     */
    public $queue = 'email';

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Execute the job.
     *
     * @phan-suppress PhanTypeArraySuspiciousNullable
     * @phan-suppress PhanPossiblyNullTypeArgumentInternal
     */
    public function handle(): void
    {
        $payload = $this->webhookCall->payload;
        $recordType = $payload['RecordType'];

        switch ($recordType) {
            case 'Bounce':
                $email = $payload['Email'];
                $reason = $payload['Type'];
                break;
            case 'SpamComplaint':
                $email = $payload['Email'];
                $reason = $payload['Type'];
                break;
            case 'SubscriptionChange':
                $email = $payload['Recipient'];
                // if this address is suppressed then set the reason
                // if it was reactivated in postmark then set to null
                $reason = $payload['SuppressSending'] ? $payload['SuppressionReason'] : null;
                break;
            default:
                throw new \Exception('Unrecognized record type '.$recordType);
        }

        $matches = [];

        $query = User::where('gt_email', '=', $email)
            ->orWhere('gmail_address', '=', $email)
            ->orWhere('clickup_email', '=', $email);

        if (preg_match('/(?P<uid>[a-z]+[0-9]+)@gatech\.edu/', $email, $matches) === 1) {
            $query = $query->orWhere('uid', '=', $matches['uid']);
        }

        $user = $query->first();

        $sponsorUser = null;

        if ($user === null) {
            $sponsorUser = SponsorUser::where('email', '=', $email)->first();
        }

        if ($user === null && $sponsorUser === null) {
            if (array_key_exists('Metadata', $payload)) {
                if (array_key_exists('transaction-id', $payload['Metadata'])) {
                    $user = DuesTransaction::where('id', '=', $payload['Metadata']['transaction-id'])->sole()->user;
                } elseif (array_key_exists('token-id', $payload['Metadata'])) {
                    $user = Token::where('id', '=', $payload['Metadata']['token-id'])->sole()->user;
                } elseif (array_key_exists('signature-id', $payload['Metadata'])) {
                    $user = Signature::where('id', '=', $payload['Metadata']['signature-id'])->sole()->user;
                } elseif (array_key_exists('payment-id', $payload['Metadata'])) {
                    $user = Payment::where('id', '=', $payload['Metadata']['payment-id'])->sole()->payable->user;
                }
            }
        }

        if ($user === null && $sponsorUser === null) {
            throw new \Exception('Could not match event to user or sponsor user. Manual match required.');
        }

        if ($user !== null) {
            $user->email_suppression_reason = $reason;
            $user->save();
        } else {
            $sponsorUser->email_suppression_reason = $reason;
            $sponsorUser->save();
        }
    }
}
