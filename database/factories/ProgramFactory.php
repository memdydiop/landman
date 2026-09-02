<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        $title = fake()->unique()->city().' - Lotissement '.$this->faker->word();

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'city' => fake()->city(),
            'address' => fake()->address(),
            'total_area' => fake()->numberBetween(5000, 50000),
            'description' => fake()->paragraphs(3, true),
            'cover_path' => null,
            'is_published' => true,
            'published_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
