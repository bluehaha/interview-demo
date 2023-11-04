<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 100),
            'stripe_id' => $this->faker->uuid,
        ];
    }
}
