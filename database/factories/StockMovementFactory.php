<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 100);

        return [
            'product_id' => Product::factory(),
            'type' => StockMovementType::In,
            'qty' => $qty,
            'balance' => $qty,
            'reference_type' => null,
            'reference_id' => null,
            'user_id' => User::factory(),
        ];
    }
}