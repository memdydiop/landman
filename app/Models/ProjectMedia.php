<?php

namespace App\Models;

use Database\Factories\ProjectMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property string $path
 * @property string $disk
 * @property string|null $mime
 * @property int|null $size
 * @property int|null $width
 * @property int|null $height
 * @property int $position
 * @property bool $is_cover
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'path', 'disk', 'mime', 'size', 'width', 'height', 'position', 'is_cover'])]
class ProjectMedia extends Model
{
    /** @use HasFactory<ProjectMediaFactory> */
    use HasFactory;

    protected $table = 'project_media';

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }
}
