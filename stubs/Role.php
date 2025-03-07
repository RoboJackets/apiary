<?php

declare(strict_types=1);

namespace Spatie\Permission\Contracts;

/**
 * @property int $id
 * @property string $name
 *
 * @method void save()
 * @method void delete()
 * @method void givePermissionTo(string $permission)
 * @method \Illuminate\Support\Collection<int,\Spatie\Permission\Models\Role> with(string $relationship)
 * @method void syncPermissions(iterable<string> $permissions)
 */
interface Role
{
}
