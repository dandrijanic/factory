<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceList>
 */
class PriceListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (!Product::where(['published' => true])->count()) {
            Product::factory()->create(['published' => true]);
        }

        return [
            'title' => $this->faker->words(2, true),
            'sku' => Product::inRandomOrder()->where(['published' => true])->first()->sku,
            'price' => $this->faker->randomNumber(7, false),
        ];
    }
}
