<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Spatie\Permission\Models\Permission;

class AdoptKiosk extends Action
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Adopt Kiosk';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Adopt';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Are you sure you want to adopt this OAuth client as a kiosk? It will be given permissions'
        .' to read user data and create attendance records.';

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\OAuth2Client>  $models
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        $client = $models->sole();

        $permissions = [
            'read-roles-and-permissions',
            'read-teams',
            'create-attendance',
            'read-users',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::findByName($permissionName);
            $client->givePermissionTo($permission);
        }

        return Action::message('The kiosk permissions were attached successfully!');
    }
}
