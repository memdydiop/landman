<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug().'_'.fake()->randomLetter(),
            'group' => fake()->randomElement(['home', 'about', 'services']),
            'value' => ['title' => fake()->sentence()],
        ];
    }
}
