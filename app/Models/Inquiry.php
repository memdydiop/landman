<?php

namespace App\Models;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Enums\ServiceType;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property InquiryType $inquiry_type
 * @property ServiceType|null $service_type
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property int|null $plot_id
 * @property int|null $program_id
 * @property string|null $message
 * @property InquiryStatus $status
 * @property array<string,mixed>|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['inquiry_type', 'service_type', 'name', 'email', 'phone', 'plot_id', 'program_id', 'message', 'status', 'meta'])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'inquiry_type' => InquiryType::class,
            'service_type' => ServiceType::class,
            'status' => InquiryStatus::class,
            'meta' => 'array',
        ];
    }

    /** @return BelongsTo<Plot, $this> */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /** @return BelongsTo<Program, $this> */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfStatus(Builder $query, InquiryStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfType(Builder $query, InquiryType $type): Builder
    {
        return $query->where('inquiry_type', $type);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', InquiryStatus::NOUVEAU);
    }
}
