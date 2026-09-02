<?php

namespace App\Models;

use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $key
 * @property string $group
 * @property array<string,mixed>|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['key', 'group', 'value'])]
class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /** @return HasMany<SiteSettingHistory, $this> */
    public function histories(): HasMany
    {
        return $this->hasMany(SiteSettingHistory::class)->latest();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        // Bypass cache in testing to ensure RefreshDatabase isolation
        if (app()->environment('testing')) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        }

        $cacheKey = "site_settings.{$key}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $setting = static::where('key', $key)->first();

        if ($setting) {
            return Cache::rememberForever($cacheKey, fn () => $setting->value);
        }

        return $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general', ?string $action = null): static
    {
        $existing = static::where('key', $key)->first();
        $oldValue = $existing?->value;
        $isCreate = ! $existing;

        /** @var static $model */
        $model = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        // Historique — uniquement si valeur change
        if ($oldValue !== $value) {
            try {
                SiteSettingHistory::create([
                    'site_setting_id' => $model->id,
                    'key' => $key,
                    'group' => $group,
                    'old_value' => $oldValue,
                    'new_value' => $value,
                    'user_id' => auth()->id(),
                    'action' => $action ?? ($isCreate ? 'create' : 'update'),
                ]);
            } catch (\Throwable $e) {
                // Ne pas bloquer la sauvegarde si historique échoue (ex: table manquante en test)
                report($e);
            }
        }

        Cache::forget("site_settings.{$key}");
        // Invalide aussi le cache vitrine (Cache::remember('services.list', 300, ...) etc.)
        // Les clés vitrine utilisent le même nom que la clé SiteSetting (ex: 'services.list',
        // 'about.hero', 'programs.hero', 'projects.hero', 'posts.hero', 'contact.hero')
        Cache::forget($key);

        // Warm cache (sauf en testing)
        if (! app()->environment('testing')) {
            Cache::rememberForever("site_settings.{$key}", fn () => $value);
        }

        return $model;
    }

    public static function restoreVersion(int $historyId): ?static
    {
        $history = SiteSettingHistory::find($historyId);
        if (! $history) {
            return null;
        }

        return static::set($history->key, $history->old_value ?? $history->new_value, $history->group, 'restore');
    }

    public static function forgetCache(string $key): void
    {
        Cache::forget("site_settings.{$key}");
        Cache::forget($key);
    }

    public static function flushCache(): void
    {
        foreach (static::pluck('key') as $k) {
            Cache::forget("site_settings.{$k}");
            Cache::forget($k);
        }
        // Purge aussi les clés vitrine connues même si pas en base (TTL 300)
        foreach (['services.list', 'services.hero', 'about.hero', 'programs.hero', 'projects.hero', 'posts.hero', 'contact.hero', 'home.featuredProjects', 'home.recentWorks', 'home.programs', 'home.availablePlots', 'home.testimonials', 'home.partners', 'home.stats'] as $k) {
            Cache::forget($k);
        }
    }
}
