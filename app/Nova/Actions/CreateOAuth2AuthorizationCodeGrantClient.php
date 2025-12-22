<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Nova\Actions;

use App\Nova\OAuth2Client;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Passport\ClientRepository;

class CreateOAuth2AuthorizationCodeGrantClient extends Action
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
    public $name = 'Create Authorization Code Grant Client';

    private const string STANDARD_CLIENT = 'standard';

    private const string PUBLIC_CLIENT = 'public';

    public function __construct(private ClientRepository $clientRepository)
    {
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\OAuth2Client>  $models
     *
     * @phan-suppress PhanPossiblyNullTypeArgumentInternal
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $client = $this->clientRepository->createAuthorizationCodeGrantClient(
            name: $fields->name,
            redirectUris: explode(',', $fields->redirect_urls),
            confidential: $fields->type !== self::PUBLIC_CLIENT
        );

        if ($client->confidential()) {
            return Action::modal(
                'client-id-and-secret-modal',
                [
                    'client_id' => $client->id,
                    'client_secret' => $client->plain_secret,
                ]
            )->withMessage('The client was created successfully!');
        } else {
            return Action::visit(substr(route(
                'nova.pages.detail',
                [
                    'resource' => OAuth2Client::uriKey(),
                    'resourceId' => $client->id,
                ],
                false
            ), 5))->withMessage('The client was created successfully!');
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Name')
                ->rules('required', 'unique:oauth_clients,name'),

            Text::make('Redirect URLs')
                ->rules('required')
                ->help('Separate multiple values with commas.'),

            Select::make('Type')
                ->options([
                    self::STANDARD_CLIENT => 'Confidential',
                    self::PUBLIC_CLIENT => 'Public',
                ])
                ->rules('required'),
        ];
    }
}
