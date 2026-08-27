<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\TextExtractionError;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

/**
 * Represents a user's uploaded resume file and the extracted text used for searching.
 *
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read string $file_name
 * @property-read string $absolute_file_path
 * @property-read string $storage_path
 * @property-read string $is_visible
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Resume whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resume whereExtractedText($value)
 */
class Resume extends Model
{
    use Searchable;

    public const string STORAGE_DISK = 'local';

    public const string STORAGE_DIRECTORY = 'resumes';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the user that owns this Resume.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Returns the name of the storage disk which contains resumes.
     * Should be used as the source of truth in resume storage.
     *
     * @psalm-pure
     */
    public static function storageDisk(): string
    {
        return self::STORAGE_DISK;
    }

    /**
     * Returns the name of the resumes directory.
     * Should be used as the source of truth in resume storage.
     *
     * @psalm-pure
     */
    public static function storageDirectory(): string
    {
        return self::STORAGE_DIRECTORY;
    }

    /**
     * Returns the storage path, complete with file name, given a User.
     * Should be used as the source of truth in resume storage.
     *
     * @psalm-pure
     *
     * @param  $user  User for this storage path.
     */
    public static function storagePathForUser(User $user): string
    {
        return self::storageDirectory().'/'.$user->uid.'.pdf';
    }

    /**
     * Returns the file name for a given User.
     * Should be used as the source of truth in resume storage.
     *
     * @psalm-pure
     *
     * @param  $user  User for this file name.
     */
    public static function fileNameForUser(User $user): string
    {
        return $user->uid.'.pdf';
    }

    /**
     * Return the storage path relative to Storage::disk('local') for the current Resume.
     *
     * @psalm-mutation-free
     */
    public function getStoragePathAttribute(): string
    {
        return self::storagePathForUser($this->user);
    }

    /**
     * Get the file name for this Resume.
     * If running on multiple Resumes, should eager load Users to avoid N+1.
     *
     * @psalm-mutation-free
     */
    public function getFileNameAttribute(): string
    {
        return $this->user->uid.'.pdf';
    }

    /**
     * File path, complete with file name, for this Resume.
     */
    public function getAbsoluteFilePathAttribute(): string
    {
        return Storage::disk(self::storageDisk())->path($this->storage_path);
    }

    /**
     * Get the is_visible flag for the Resume.
     *
     * @psalm-mutation-free
     */
    public function getIsVisibleAttribute(): bool
    {
        return self::where('id', $this->id)->visible()->count() !== 0;
    }

    /**
     * Scope a query to automatically include only visible resumes.
     * Visible: Resume is for a user who has paid dues for a currently ongoing term
     *         or, has a non-zero payment for an active DuesPackage.
     *         Additionally, the Resume's user is a student.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereHas('user', static function (Builder $q): void {
            $q->active()
                ->where('primary_affiliation', 'student')
                ->where('is_service_account', '=', false)
                ->whereDoesntHave('duesPackages', static function (Builder $q): void {
                    $q->where('restricted_to_students', false);
                });
        });
    }

    /**
     * Get the indexable data array for the model.
     * Uses Apache Tika to extract text from a resume if the file exists.
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        if (Storage::disk(self::storageDisk())->exists($this->storage_path)
            && Storage::disk(self::storageDisk())->size($this->storage_path) > 0) {
            try {
                $full_text = new Client(
                    [
                        'base_uri' => config('services.tika.url'),
                        'headers' => [
                            'Accept' => 'text/plain',
                            'Content-Type' => 'application/octet-stream',
                        ],
                        'allow_redirects' => false,
                        'connect_timeout' => 10,
                        'read_timeout' => 60,
                        'synchronous' => true,
                    ]
                )->put(
                    '/tika',
                    [
                        'body' => Storage::disk(self::storageDisk())->get($this->storage_path),
                    ]
                )->getBody()->getContents();
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                throw new TextExtractionError(
                    'Failed to extract text for resume '.$this->id.': '.$e->getMessage(),
                    previous: $e
                );
            }
            if ($full_text === '') {
                throw new TextExtractionError(
                    'Failed to extract text for resume '.$this->id.': Tika returned empty response.'
                );
            }
        } else {
            $full_text = '';
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'file_name' => $this->file_name,
            'storage_path' => $this->storage_path,
            'absolute_file_path' => $this->absolute_file_path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'extracted_text' => $full_text,
        ];
    }
}
