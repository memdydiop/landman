<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $path
 * @property string|null $route
 * @property string|null $ip
 * @property string|null $user_agent
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['path', 'route', 'ip', 'user_agent', 'user_id'])]
class PageVisit extends Model
{
    use Prunable;

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        // Purge auto > 90 jours (RGPD + volume). Configurable via env ANALYTICS_RETENTION_DAYS
        $days = (int) config('analytics.retention_days', 90);

        /** @var Builder<static> $query */
        $query = static::where('created_at', '<=', now()->subDays($days));

        return $query;
    }
}
