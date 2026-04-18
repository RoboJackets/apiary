<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

/**
 * Represents a user's uploaded resume file and the extracted text used for searching.
 *
 * @property int $id
 * @property string $user_id
 * @property string|null $file_name
 * @property string|null $extracted_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Resume whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resume whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resume whereExtractedText($value)
 */
class Resume extends Model
{
    use HasFactory;
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
     * The attributes that should be cast to native types.
     *
     * @return array<string,string>
     *
     * @psalm-pure
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'extracted_text' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @psalm-pure */
    public static function storageDisk(): string
    {
        return self::STORAGE_DISK;
    }

    /** @psalm-pure */
    public static function storageDirectory(): string
    {
        return self::STORAGE_DIRECTORY;
    }

    /** @psalm-pure */
    public static function storagePathForUser(User $user): string
    {
        return self::storageDirectory().'/'.$user->uid.'.pdf';
    }

    /** @psalm-mutation-free */
    public function storagePath(): string
    {
        return self::storageDirectory().'/'.$this->file_name;
    }

    public function getFilePathAttribute(): string
    {
        return Storage::disk(self::storageDisk())->path($this->storagePath());
    }

    /**
     * Get the indexable data array for the model.
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
            'file_name' => $this->file_name ?? '',
            'extracted_text' => $this->extracted_text ?? '',
        ];
    }

    public function scopeSearchText(Builder $query, string $text): Builder
    {
        if (trim($text) === '') {
            return $query;
        }

        return $query->where('extracted_text', 'like', '%'.$text.'%');
    }
}
