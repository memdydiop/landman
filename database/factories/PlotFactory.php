<?php

namespace Database\Factories;

use App\Enums\PlotStatus;
use App\Models\Plot;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plot>
 */
class PlotFactory extends Factory
{
    protected $model = Plot::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'reference' => 'LOT-'.strtoupper(fake()->unique()->bothify('??##??')),
            'surface_m2' => fake()->numberBetween(250, 1200),
            'price' => fake()->numberBetween(1500000, 15000000),
            'status' => fake()->randomElement(PlotStatus::cases()),
            'is_viabilise' => fake()->boolean(80),
            'juridical_status' => fake()->randomElement(['ACD', 'Titre foncier', 'En cours']),
            'plan_pdf_path' => null,
            'latitude' => fake()->latitude(5.2, 5.6),
            'longitude' => fake()->longitude(-4.2, -3.9),
            'published_at' => now(),
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PlotStatus::DISPONIBLE,
        ]);
    }

    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PlotStatus::VENDU,
        ]);
    }
}
