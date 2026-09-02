<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::creating(function (Model $model): void {
            $slug = $model->getAttribute('slug');
            $title = $model->getAttribute('title');
            if (empty($slug)) {
                $model->setAttribute('slug', static::generateUniqueSlug((string) $title));
            } else {
                $model->setAttribute('slug', static::generateUniqueSlug((string) $slug));
            }
        });

        static::updating(function (Model $model): void {
            $key = $model->getKey();
            $ignoreId = is_int($key) ? $key : (is_numeric($key) ? (int) $key : null);
            $slug = $model->getAttribute('slug');
            $origSlug = $model->getOriginal('slug');
            if ($model->isDirty('title') && empty($origSlug) && empty($slug)) {
                $model->setAttribute('slug', static::generateUniqueSlug((string) $model->getAttribute('title'), $ignoreId));
            } elseif ($model->isDirty('slug') && ! empty($slug)) {
                $model->setAttribute('slug', static::generateUniqueSlug((string) $slug, $ignoreId));
            }
        });
    }

    protected static function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $slug = Str::slug($value);
        $original = $slug;
        $i = 1;
        while (static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original.'-'.$i++;
        }

        return $slug;
    }
}
