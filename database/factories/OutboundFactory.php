<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutboundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'destination' => fake()->company(),
            'user_id' => User::factory(),
            'status' => DocumentStatus::Completed,
        ];
    }
}