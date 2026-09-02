<?php

namespace App\Models;

use App\Enums\PlotStatus;
use Database\Factories\PlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $program_id
 * @property string $reference
 * @property string $surface_m2
 * @property string|null $price
 * @property PlotStatus $status
 * @property bool $is_viabilise
 * @property string|null $juridical_status
 * @property string|null $plan_pdf_path
 * @property string|null $latitude
 * @property string|null $longitude
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['program_id', 'reference', 'surface_m2', 'price', 'status', 'is_viabilise', 'juridical_status', 'plan_pdf_path', 'latitude', 'longitude', 'published_at'])]
class Plot extends Model
{
    /** @use HasFactory<PlotFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => PlotStatus::class,
            'surface_m2' => 'decimal:2',
            'price' => 'decimal:2',
            'is_viabilise' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Program, $this> */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /** @return HasMany<Inquiry, $this> */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', PlotStatus::DISPONIBLE);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfStatus(Builder $query, PlotStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function isAvailable(): bool
    {
        return $this->status === PlotStatus::DISPONIBLE;
    }
}
