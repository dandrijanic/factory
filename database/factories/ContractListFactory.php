<?php

namespace Database\Factories;

use App\Models\ContractList;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContractList>
 */
class ContractListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (!User::count()) {
            User::factory()->create();
        }

        if (!Product::where(['published' => true])->count()) {
            Product::factory()->create(['published' => true]);
        }


        return [
            'user_id' => User::first(),
            'sku' => Product::inRandomOrder()->where(['published' => true])->first()->sku,
            'price' => $this->faker->randomNumber(7, false),
        ];
    }
}
