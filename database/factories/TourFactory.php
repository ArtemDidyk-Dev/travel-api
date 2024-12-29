<?php

namespace Database\Factories;

use App\Models\Travel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->text(20),
            'travel_id' =>Travel::factory(),
            'start_date' => now(),
            'end_date' => now()->addDays(rand(1, 10)),
            'price' => $this->faker->randomFloat(2, 10, 999),
        ];
    }
}
