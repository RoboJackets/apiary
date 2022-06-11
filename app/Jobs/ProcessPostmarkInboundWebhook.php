<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
// phpcs:disable Generic.Commenting.Todo.TaskFound

namespace App\Jobs;

use App\Models\TravelAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessPostmarkInboundWebhook extends ProcessWebhookJob
{
    private const SIGNER_INFO_REGEX = '/Using IP Address: (?P<ipAddress>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s+Sent: (?P<sentAt>\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{1,2}:\d{1,2} (AM|PM))\s+Viewed: (?P<viewedAt>\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{1,2}:\d{1,2} (AM|PM))\s+Signed: (?P<signedAt>\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{1,2}:\d{1,2} (AM|PM))/';

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
     */
    public function handle(): void
    {
        $payload = $this->webhookCall->payload;
        $subject = $payload['Subject'];

        if (Str::startsWith($subject, 'Completed: ')) {
            $summary = collect($payload['Attachments'])->firstOrFail(static function (array $value, int $key): bool {
                return 'Summary.pdf' === $value['Name'];
            });

            $decoded = base64_decode($summary['Content'], true);

            $parser = new Parser();

            $pdf = $parser->parseContent($decoded);

            $text = $pdf->getText();

            $user = User::where(
                'uid',
                '=',
                self::getValueWithRegex(
                    '/Signed by link sent to (?P<uid>[a-z]+[0-9]+)@gatech\.edu/',
                    $text,
                    'uid'
                )
            )->sole();

            $envelope = $user->envelopes()->where('complete', false)->sole();

            $envelope->sent_at = self::getValueWithRegex(
                self::SIGNER_INFO_REGEX,
                $text,
                'sentAt'
            );

            $envelope->viewed_at = self::getValueWithRegex(
                self::SIGNER_INFO_REGEX,
                $text,
                'viewedAt'
            );

            $envelope->signed_at = self::getValueWithRegex(
                self::SIGNER_INFO_REGEX,
                $text,
                'signedAt'
            );

            $envelope->completed_at = self::getValueWithRegex(
                '/Completed\s+Security Checked\s+(?P<completedAt>\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{1,2}:\d{1,2} (AM|PM))/',
                $text,
                'completedAt'
            );

            $envelope->signer_ip_address = self::getValueWithRegex(
                self::SIGNER_INFO_REGEX,
                $text,
                'ipAddress'
            );

            $envelope->envelope_id = self::getValueWithRegex(
                '/Envelope Id: (?P<envelopeId>[A-Z0-9]{32})/',
                $text,
                'envelopeId'
            );

            $envelope->url = self::getValueWithRegex(
                '/(?P<url>https:\/\/na3.docusign.net\/Member\/EmailStart.aspx.+)/',
                $payload['TextBody'],
                'url',
                'email text'
            );

            Storage::makeDirectory($envelope->envelope_id);
            Storage::disk('local')->put($envelope->envelope_id.'/Summary.pdf', $decoded);

            $envelope->complete = true;
            $envelope->save();

            if ($envelope->signable_type === TravelAssignment::getMorphClassStatic()) {
                $envelope->signable->tar_received = true;
                $envelope->signable->save();
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
        string $from = 'summary PDF'
    ): string {
        $matches = [];

        if (1 !== preg_match($regex, $text, $matches)) {
            throw new \Exception('Could not extract '.$groupName.' from '.$from);
        }

        return $matches[$groupName];
    }
}
