<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role' => fake()->jobTitle(),
            'content' => fake()->paragraph(),
            'rating' => fake()->numberBetween(4, 5),
            'avatar_path' => null,
            'is_published' => true,
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
