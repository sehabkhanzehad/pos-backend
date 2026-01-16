<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_no' => $this->faker->unique()->numerify('ORD-#####'),
            'status' => $this->faker->randomElement(OrderStatus::cases()),
            'total_amount' => $this->faker->randomFloat(2, 10, 10000),
        ];
    }
}
