<?php

declare(strict_types=1);

namespace App\Models;

use App\Util\Sentry;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
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
     * Modify the collection of models being made searchable.
     */
    public function makeSearchableUsing(Collection $models): Collection
    {
        return $models->load('user');
    }

    /**
     * Get the indexable data array for the model.
     * Uses Apache Tika to extract text from a resume if the file exists.
     *
     * @psalm-mutation-free
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        $file_path = $this->file_path;

        $full_text = '';

        if (Storage::disk('local')->exists($file_path) && Storage::disk('local')->size($file_path) > 0) {
            $file_hash = hash_file('sha512', Storage::disk('local')->path($file_path));

            $full_text = Cache::lock(name: 'tika_extraction_'.$file_hash, seconds: 360)->block(
                seconds: 330,
                callback: static function () use ($file_hash, $file_path): string {
                    return Cache::rememberForever(
                        'tika_file_'.$file_hash,
                        static fn (): string => Sentry::wrapWithChildSpan(
                            'tika.extract',
                            static fn (): string => (new Client(
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
                            ))->put(
                                '/tika',
                                [
                                    'body' => Storage::disk('local')->get($file_path),
                                ]
                            )->getBody()->getContents()
                        )
                    );
                }
            );
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'extracted_text' => $full_text,
        ];
    }
}
