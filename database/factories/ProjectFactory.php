<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'service_type' => fake()->randomElement(ServiceType::cases()),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'location' => fake()->city(),
            'surface_m2' => fake()->numberBetween(100, 5000),
            'duration_months' => fake()->numberBetween(3, 24),
            'year' => fake()->numberBetween(2020, 2026),
            'description' => fake()->paragraphs(3, true),
            'technical_sheet' => [
                'maitre_ouvrage' => fake()->company(),
                'budget' => fake()->numberBetween(50000, 2000000).' €',
            ],
            'cover_path' => null,
            'is_featured' => fake()->boolean(30),
            'is_published' => true,
            'published_at' => now()->subDays(fake()->numberBetween(0, 90)),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_published' => true,
        ]);
    }
}
