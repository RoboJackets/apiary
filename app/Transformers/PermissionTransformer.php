<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Permission;
use Auth;

class PermissionTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "roles",
        "users",
    ];
        
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Permission $permission)
    {
        return [
            "id" => $permission->id,
            "name" => $permission->name,
            "guard_name" => $permission->guard_name
        ];
    }

    public function includeRoles(Permission $permission)
    {
        return $this->collection($permission->roles, new RoleTransformer());
    }

    public function includeUsers(Permission $permission)
    {
        return $this->collection($permission->users, new UserTransformer());
    }
}
