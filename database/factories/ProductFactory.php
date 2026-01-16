<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'sku' => $this->faker->unique()->ean13(),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'stock_qty' => $this->faker->numberBetween(0, 1000),
            'low_stock_threshold' => $this->faker->numberBetween(5, 50),
            'description' => $this->faker->sentence(),
        ];
    }
}
