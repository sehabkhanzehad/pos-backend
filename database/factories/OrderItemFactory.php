<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = $this->faker->numberBetween(1, 10);
        $unit_price = $this->faker->randomFloat(2, 1, 500);

        return [
            'qty' => $qty,
            'unit_price' => $unit_price,
            'sub_total' => $qty * $unit_price,
        ];
    }
}
