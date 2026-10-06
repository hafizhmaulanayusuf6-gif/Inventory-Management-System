<?php

namespace Database\Factories;

use App\Models\Inbound;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InboundItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inbound_id' => Inbound::factory(),
            'product_id' => Product::factory(),
            'qty' => fake()->numberBetween(1, 100),
        ];
    }
}