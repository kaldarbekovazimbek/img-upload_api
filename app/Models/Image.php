<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $hash
 * @property string $mime_type
 * @property string $name
 * @property string $path
 * @property string $disk
 * @property integer $original_size
 * @property integer $size
 * @property integer $reference_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */

class Image extends Model
{
    protected $fillable = [
        'name',
        'path',
        'disk',
        'mime_type',
        'hash',
        'original_size',
        'size',
        'reference_count',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_images');
    }
}
