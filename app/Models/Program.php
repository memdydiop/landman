<?php

namespace App\Models;

use App\Concerns\HasUniqueSlug;
use App\Enums\PlotStatus;
use Database\Factories\ProgramFactory;
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
 * @property string $city
 * @property string|null $address
 * @property string|null $total_area
 * @property string|null $description
 * @property string|null $cover_path
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['title', 'slug', 'city', 'address', 'total_area', 'description', 'cover_path', 'is_published', 'published_at'])]
class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory, HasUniqueSlug, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'total_area' => 'decimal:2',
        ];
    }

    /** @return HasMany<Plot, $this> */
    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class);
    }

    /** @return HasMany<Plot, $this> */
    public function availablePlots(): HasMany
    {
        return $this->hasMany(Plot::class)->where('status', PlotStatus::DISPONIBLE->value);
    }

    /** @return HasMany<Inquiry, $this> */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
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
    public function scopeInCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }
}
