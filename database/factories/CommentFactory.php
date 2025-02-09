<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text' => fake()->text(1000),
            'is_public' => $this->faker->boolean(),
            'tour_id' => Tour::inRandomOrder()->value('id'),
            'user_id' => User::inRandomOrder()->value('id'),
        ];
    }
}
