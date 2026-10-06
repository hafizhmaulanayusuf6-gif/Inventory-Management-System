<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => 'SKU-' . strtoupper(fake()->unique()->bothify('??-####')),
            'name' => fake()->unique()->words(3, true),
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'stock' => 0, // sesuai PRD: stok awal selalu 0
            'min_stock' => fake()->numberBetween(0, 10),
            'price' => fake()->numberBetween(1000, 500000),
        ];
    }

    /**
     * Khusus untuk kebutuhan test: set stok awal tertentu.
     * Di aplikasi nyata stok hanya berubah lewat StockService.
     */
    public function withStock(int $stock): static
    {
        return $this->state(fn () => ['stock' => $stock]);
    }
}