<?php

namespace App\Models;

use App\Concerns\HasUniqueSlug;
use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property ServiceType $service_type
 * @property ProjectStatus $status
 * @property string|null $location
 * @property string|null $surface_m2
 * @property int|null $duration_months
 * @property int|null $year
 * @property string|null $description
 * @property array<string,mixed>|null $technical_sheet
 * @property string|null $cover_path
 * @property bool $is_featured
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['title', 'slug', 'service_type', 'status', 'location', 'surface_m2', 'duration_months', 'year', 'description', 'technical_sheet', 'cover_path', 'is_featured', 'is_published', 'published_at'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasUniqueSlug, SoftDeletes;

    protected function casts(): array
    {
        return [
            'service_type' => ServiceType::class,
            'status' => ProjectStatus::class,
            'technical_sheet' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'surface_m2' => 'decimal:2',
        ];
    }

    /** @return HasMany<ProjectMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(ProjectMedia::class)->orderBy('position');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfService(Builder $query, ServiceType $type): Builder
    {
        return $query->where('service_type', $type);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfStatus(Builder $query, ProjectStatus $status): Builder
    {
        return $query->where('status', $status);
    }
}
