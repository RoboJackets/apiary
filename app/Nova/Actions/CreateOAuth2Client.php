<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
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
    use InteractsWithQueue, Queueable;

    public $name = 'Create OAuth2 Client';

    /**
     * @var ClientRepository Used to create new OAuth2 clients in Passport
     */
    private ClientRepository $clientRepository;
    private const STANDARD_CLIENT = 'standard';
    private const PUBLIC_CLIENT = 'public';
    private const PASSWORD_ACCESS_CLIENT = 'password_client';

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
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return array|string[]
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (sizeof($models) > 1) {
            return Action::danger('This action can only be run on one model at a time.');
        }

        $user = $models[0];

        $clientType = $fields->client_type;
        $personalAccessClient = false; // Deliberately unsupported - creating a personal access client only has
            // to be done once and the client ID/secret must be added as environment variables
        $passwordGrantClient = false; // We don't support this right now
        $confidential = $clientType !== self::PUBLIC_CLIENT; // confidential means the client has a secret

        $client = $this->clientRepository->create($user->id, $fields->client_name, $fields->redirect_urls, null,
            $personalAccessClient, $passwordGrantClient, $confidential);

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
     * @return array
     */
    public function fields()
    {
        return [
            Heading::make('<p>To avoid issues, let the outer page load fully before clicking Run Action.</p>')->asHtml(),
            Text::make('Client Name')->rules('required'),
            Text::make('Redirect URLs')->rules('required')
                ->help('Separate multiple values with commas.  Example: https://example.com,https://invalid.url'),
            Heading::make('<p>Client Types:<ul><li><strong>Standard Client</strong> - Use for most'.
                ' web use cases <em>except</em> single-page JavaScript applications (e.g., Vue.js or React)'.
                ' or other use cases where the client secret cannot be kept secret on the backend.</li>'.
                ' <li><strong>Public (PKCE-Enabled) Client</strong> - Use for mobile applications or uses not suitable for a standard'.
                ' client. </li>'.
                ' <li><strong>Password Grant Clients</strong> - Currently not supported due to technical limitations.'.
                '</li>'.
                '</ul></p>')->asHtml(),
            Select::make('Client Type')->options([
                self::STANDARD_CLIENT => 'Standard Client (recommended)',
                self::PUBLIC_CLIENT => 'Public (PKCE-Enabled) Client',
            ])
                ->rules('required'),
        ];
    }
}
