<?php

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Enums\ServiceType;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    public function definition(): array
    {
        return [
            'inquiry_type' => fake()->randomElement(InquiryType::cases()),
            'service_type' => fake()->randomElement(ServiceType::cases()),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'plot_id' => null,
            'program_id' => null,
            'message' => fake()->paragraph(),
            'status' => InquiryStatus::NOUVEAU,
            'meta' => null,
        ];
    }

    public function treated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InquiryStatus::TRAITE,
        ]);
    }
}
