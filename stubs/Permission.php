<?php

declare(strict_types=1);

namespace Spatie\Permission\Contracts;

/**
 * @property int $id
 * @property string $name
 *
 * @method void save()
 * @method void delete()
 * @method \Illuminate\Support\Collection<int,\Spatie\Permission\Models\Permission> with(string $relationship)
 */
interface Permission
{
}
