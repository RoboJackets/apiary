<?php

/**
 * This model represents a sponsor entity in the application.
 * It includes attributes such as name, start and end dates of the sponsorship, and authorized domain names.
 * It also provides methods to check if the sponsor is currently active and if a given email is authorized based on its domain.
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property array $domain_names
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * 
 */
class Sponsor extends Model
{
    use HasFactory;
    use SoftDeletes;


/**
 * The attributes that are not mass assignable.
 */
    protected $guarded = ['id',
        'created_at',
        'updated_at',
        'deleted_at',
        'name',
        'start_date',
        'end_date',
        'domain_names'
];


/**
 * The attributes that should be cast to native types.
 */
    #[\Override]
    protected function casts(): array {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'domain_names' => 'array',
        ];
    }

    /**
     * Get the active status of the sponsor.
     */
    public function active(): boolean
    {
        return $this->end_date > now() && $this->start_date < now();
    }

    /**
     * Check if the given email is authorized for the sponsor.
     */
    public function authorized($email): boolean
    {
        $domain = substr(strrchr($email, "@"), 1);
        return in_array($domain, $this->domain_names);
    }
}