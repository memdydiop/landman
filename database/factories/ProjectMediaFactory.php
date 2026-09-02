<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectMedia>
 */
class ProjectMediaFactory extends Factory
{
    protected $model = ProjectMedia::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'path' => 'projects/'.fake()->uuid().'.jpg',
            'disk' => 'public',
            'mime' => 'image/jpeg',
            'size' => fake()->numberBetween(50000, 2000000),
            'width' => 1920,
            'height' => 1080,
            'position' => fake()->numberBetween(0, 10),
            'is_cover' => false,
        ];
    }

    public function cover(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cover' => true,
            'position' => 0,
        ]);
    }
}
