<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use App\Models\SiteSettingHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSettingHistory>
 */
class SiteSettingHistoryFactory extends Factory
{
    protected $model = SiteSettingHistory::class;

    public function definition(): array
    {
        return [
            'site_setting_id' => SiteSetting::factory(),
            'key' => fake()->slug(),
            'group' => fake()->randomElement(['home', 'about', 'services', 'seo', 'theme']),
            'old_value' => ['title' => fake()->sentence()],
            'new_value' => ['title' => fake()->sentence()],
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['create', 'update', 'restore']),
        ];
    }
}
