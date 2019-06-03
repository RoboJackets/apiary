<?php declare(strict_types = 1);

namespace App;

use Laravel\Nova\Actions\Actionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTemplate extends Model
{
    use Actionable;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['created_by'];

    /**
     * Get the user that owns the template.
     */
    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'creator' => 'users',
        ];
    }
}
