<?php

namespace App\Models;

use Database\Factories\SiteSettingHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $site_setting_id
 * @property string $key
 * @property string $group
 * @property array<string,mixed>|null $old_value
 * @property array<string,mixed>|null $new_value
 * @property int|null $user_id
 * @property string $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SiteSetting|null $siteSetting
 * @property-read User|null $user
 */
class SiteSettingHistory extends Model
{
    /** @use HasFactory<SiteSettingHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'site_setting_id',
        'key',
        'group',
        'old_value',
        'new_value',
        'user_id',
        'action',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    /** @return BelongsTo<SiteSetting, $this> */
    public function siteSetting(): BelongsTo
    {
        return $this->belongsTo(SiteSetting::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function diff(): array
    {
        $old = $this->old_value ?? [];
        $new = $this->new_value ?? [];

        // Shallow diff for display
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $diff = [];
        foreach ($keys as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;
            if ($ov !== $nv) {
                $diff[$k] = ['old' => $ov, 'new' => $nv];
            }
        }

        return $diff;
    }
}
