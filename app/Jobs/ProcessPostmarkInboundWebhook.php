<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Jobs;

use App\Models\Signature;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Notifications\MembershipAgreementDocuSignEnvelopeReceived;
use App\Notifications\Travel\DocuSignEnvelopeReceived;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessPostmarkInboundWebhook extends ProcessWebhookJob
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
     * @phan-suppress PhanPossiblyFalseTypeArgument
     * @phan-suppress PhanTypeMismatchArgumentNullable
     */
    public function handle(): void
    {
        $payload = $this->webhookCall->payload;
        $subject = $payload['Subject'];

        if ($subject === 'Test subject') {
            return;
        }

        if (Str::startsWith($subject, 'Completed: ')) {
            $summary = collect($payload['Attachments'])->firstOrFail(
                static fn (array $value, int $key): bool => $value['Name'] === 'Summary.pdf'
            );

            $decoded = base64_decode($summary['Content'], true);

            $parser = new Parser();

            $pdf = $parser->parseContent($decoded);

            $text = $pdf->getText();

            $maybeUid = self::getValueWithRegex(
                '/[a-zA-Z]\n\n(?P<uid>[a-z]+[0-9]+)@gatech\.edu/',
                $text,
                'uid',
                'summary PDF',
                false
            );

            $user = User::where('uid', '=', $maybeUid)->sole();

            $envelope = $user->envelopes()->where('complete', false)->sole();

            $envelope->envelope_id = self::getValueWithRegex(
                '/Envelope Id: (?P<envelopeId>[A-Z0-9]{32})/',
                $text,
                'envelopeId'
            );

            Storage::makeDirectory('docusign/'.$envelope->envelope_id);

            collect($payload['Attachments'])->each(static function (array $value, int $key) use ($envelope): void {
                $original_filename = self::getValueWithRegex(
                    '/(?P<filename>^[a-zA-Z .-]+$)/',
                    $value['Name'],
                    'filename',
                    'PDF filename'
                );

                $disk_path = 'docusign/'.$envelope->envelope_id.'/'.$original_filename;

                // @phan-suppress-next-line PhanTypeMismatchArgumentNullable
                if (Str::contains($original_filename, 'Summary')) {
                    $envelope->summary_filename = $disk_path;
                // @phan-suppress-next-line PhanTypeMismatchArgumentNullable
                } elseif (Str::contains($original_filename, 'COVID')) {
                    $envelope->covid_risk_filename = $disk_path;
                // @phan-suppress-next-line PhanTypeMismatchArgumentNullable
                } elseif (Str::contains($original_filename, 'Authority')) {
                    $envelope->travel_authority_filename = $disk_path;
                // @phan-suppress-next-line PhanTypeMismatchArgumentNullable
                } elseif (Str::contains($original_filename, 'Airfare')) {
                    $envelope->direct_bill_airfare_filename = $disk_path;
                // @phan-suppress-next-line PhanTypeMismatchArgumentNullable
                } elseif (Str::contains($original_filename, 'Agreement')) {
                    $envelope->membership_agreement_filename = $disk_path;
                } else {
                    throw new \Exception('Unable to determine column for attachment named '.$original_filename);
                }

                // @phan-suppress-next-line PhanPossiblyFalseTypeArgument
                Storage::disk('local')->put($disk_path, base64_decode($value['Content'], true));
            });

            $sender_name = self::getValueWithRegex(
                '/This message was sent to you by (?P<sender>.+) who is using the DocuSign Electronic Signature Service/',
                $payload['TextBody'],
                'sender',
                'email text'
            );

            $sender_user = User::search($sender_name)->first();

            $envelope->sent_by = $sender_user->id;

            $envelope->complete = true;
            $envelope->save();

            if ($envelope->signable_type === TravelAssignment::getMorphClassStatic()) {
                $envelope->signable->tar_received = true;
                $envelope->signable->save();

                $envelope->signedBy->notify(new DocuSignEnvelopeReceived($envelope));
            } elseif ($envelope->signable_type === Signature::getMorphClassStatic()) {
                $envelope->signable->complete = true;
                $envelope->signable->save();

                $envelope->signedBy->notify(new MembershipAgreementDocuSignEnvelopeReceived($envelope));
            } else {
                throw new \Exception('Unrecognized signable_type '.$envelope->signable_type);
            }
        } else {
            throw new \Exception('Unrecognized subject line');
        }
    }

    private static function getValueWithRegex(
        string $regex,
        string $text,
        string $groupName,
        string $from = 'summary PDF',
        bool $fail = true
    ): ?string {
        $matches = [];

        if (preg_match($regex, $text, $matches) !== 1) {
            if ($fail) {
                throw new \Exception('Could not extract '.$groupName.' from '.$from);
            }

            return null;
        }

        return $matches[$groupName];
    }
}
