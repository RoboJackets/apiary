<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Passport\ClientRepository;

class CreateOAuth2Client extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Create OAuth2 Client';

    private ClientRepository $clientRepository;

    private const STANDARD_CLIENT = 'standard';
    private const PUBLIC_CLIENT = 'public';

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
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
     * @return array<string, string>
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (1 < count($models)) {
            return Action::danger('This action can only be run on one model at a time.');
        }

        $user = $models[0];

        $clientType = $fields->client_type;
        $personalAccessClient = false; // Deliberately unsupported - creating a personal access client only has
        // to be done once and the client ID/secret must be added as environment variables
        $passwordGrantClient = false; // We don't support this right now
        $confidential = self::PUBLIC_CLIENT !== $clientType; // Confidential means the client has a secret

        $client = $this->clientRepository->create(
            $user->id,
            $fields->client_name,
            $fields->redirect_urls,
            null,
            $personalAccessClient,
            $passwordGrantClient,
            $confidential
        );

        // Side note, in case anyone ever has to debug why it seems like these `flash` calls aren't working, it's
        // probably not `flash` that's the issue. On the Nova user page where this action was originally implemented,
        // Nova makes a lot of requests as it loads various data. Especially locally, this might take 30+ seconds.
        // If you submit the action before all the Nova requests finish, one of them might consume the flashes
        // before they can be shown in the Blade template. The solution? Be more patient, or change the code below
        // to store values in the session rather than flashing.
        Session::flash('client_id', $client->id);
        Session::flash('client_confidential', $client->confidential());
        Session::flash('client_plain_secret', $client->plain_secret);

        return Action::redirect(route('oauth2.client.created'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields()
    {
        return [
            Heading::make('<p>To avoid issues, let the outer page load fully before clicking "Create Client."</p>')
                ->asHtml(),

            Text::make('Client Name')
                ->rules('required'),

            Text::make('Redirect URLs')
                ->rules('required')
                ->help('Separate multiple values with commas. Example: https://example.com,https://invalid.url'),

            Heading::make('<p>Read about client types <a href="https://oauth.net/2/client-types/">here</a>.')
                ->asHtml(),

            Select::make('Client Type')
                ->options([
                    self::STANDARD_CLIENT => 'Confidential',
                    self::PUBLIC_CLIENT => 'Public',
                ])
                ->rules('required'),
        ];
    }
}
