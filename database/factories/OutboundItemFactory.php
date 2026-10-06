<?php

namespace Database\Factories;

use App\Models\Outbound;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutboundItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'outbound_id' => Outbound::factory(),
            'product_id' => Product::factory(),
            'qty' => fake()->numberBetween(1, 100),
        ];
    }
}