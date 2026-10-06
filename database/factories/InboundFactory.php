<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InboundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'supplier_id' => Supplier::factory(),
            'reference_no' => 'IN-' . fake()->unique()->numerify('########'),
            'user_id' => User::factory(),
            'status' => DocumentStatus::Completed,
        ];
    }
}