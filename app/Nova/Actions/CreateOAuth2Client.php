<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Nova\Actions;

use App\Models\User;
use App\Nova\OAuth2Client;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Passport\ClientRepository;

class CreateOAuth2Client extends Action
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
    public $name = 'Create Client';

    private const string STANDARD_CLIENT = 'standard';

    private const string PUBLIC_CLIENT = 'public';

    public function __construct(private ClientRepository $clientRepository)
    {
    }

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $clientType = $fields->type;
        $personalAccessClient = false; // Deliberately unsupported - creating a personal access client only has
        // to be done once and the client ID/secret must be added as environment variables
        $passwordGrantClient = false; // We don't support this right now
        $confidential = $clientType !== self::PUBLIC_CLIENT; // Confidential means the client has a secret

        $client = $this->clientRepository->create(
            $fields->user,
            $fields->name,
            $fields->redirect_urls,
            null,
            $personalAccessClient,
            $passwordGrantClient,
            $confidential
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
                ->rules('required'),

            Text::make('Redirect URLs')
                ->rules('required')
                ->help('Separate multiple values with commas.'),

            Select::make('Type')
                ->options([
                    self::STANDARD_CLIENT => 'Confidential',
                    self::PUBLIC_CLIENT => 'Public',
                ])
                ->rules('required'),

            Select::make('User', 'user')
                ->options(
                    static fn (): array => User::accessActive()
                        ->get()
                        ->mapWithKeys(static fn (User $user): array => [strval($user->id) => $user->name])
                        ->toArray()
                )
                ->default(static fn (NovaRequest $r): int => intval($r->viaResourceId ?? $r->user()->id))
                ->searchable()
                ->required()
                ->rules('required'),
        ];
    }
}
