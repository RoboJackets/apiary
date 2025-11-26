<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Passport\ClientRepository;

class CreateOAuth2ClientCredentialsGrantClient extends Action
{
    /**
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    /**
     * Indicates if the action can be run without any models.
     *
     * @var bool
     */
    public $standalone = true;

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Create';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = '';

    /**
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Create Client Credentials Grant Client';

    public function __construct(private ClientRepository $clientRepository)
    {
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $client = $this->clientRepository->createClientCredentialsGrantClient(name: $fields->name);

        return Action::modal(
            'client-id-and-secret-modal',
            [
                'client_id' => $client->id,
                'client_secret' => $client->plain_secret,
            ]
        )->withMessage('The client was created successfully!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Name')
                ->rules('required', 'unique:oauth_clients,name'),
        ];
    }
}
