<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable Generic.Commenting.DocComment.MissingShort
// phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion

namespace App\Nova\Actions;

use App\Util\DocuSign;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Model\Envelope;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class VoidDocuSignEnvelope extends DestructiveAction
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Void Envelope';

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Void';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Are you sure you want to void this envelope? This is not reversible.';

    /**
     * Perform the action on the given models.
     *
     * @param  Collection<int,\App\Models\DocuSignEnvelope>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $envelope = $models->sole();

        if ($envelope->sentBy === null) {
            $docusign = new EnvelopesApi(DocuSign::getApiClient());

            $docusign->update(
                account_id: config('docusign.account_id'),
                envelope_id: $envelope->envelope_id,
                envelope: (new Envelope())
                    ->setStatus('voided')
                    ->setVoidedReason($fields->void_reason)
            );

            return self::message('The envelope was successfully voided!');
        }

        return self::danger('Sender credentials are not available!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Void Reason')
                ->required()
                ->rules('required')
                ->help('This reason will be visible to the recipient.'),
        ];
    }
}
