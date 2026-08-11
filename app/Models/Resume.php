<?php

declare(strict_types=1);

namespace App\Models;

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
 * @property \Illuminate\Support\Carbon|null $last_uploaded_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read string $file_name
 * @property-read string $file_path
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
        'last_uploaded_at',
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
     * Return the storage path for the current Resume.
     *
     * @psalm-mutation-free
     */
    public function storagePath(): string
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
    public function getFilePathAttribute(): string
    {
        return Storage::disk(self::storageDisk())->path($this->storagePath());
    }

    /**
     * Get the indexable data array for the model.
     * Will likely require Apache Tika.
     *
     * @psalm-mutation-free
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'file_name' => $this->file_name,
            'extracted_text' => '', // TODO: Extract text at index time
        ];
    }
}
