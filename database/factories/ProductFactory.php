<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'title' => $this->faker->words(4, true),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomNumber(7, false),
            'sku' => Str::random(10),
            'sku' => $this->faker->unique()->ean8,
            'published' => $this->faker->boolean,
            'published_at' => $this->faker->dateTime,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            if (!Category::count()) {
                Category::factory(10)->create();
            }

            Category::all()->random(rand(1, 5))->each(function ($category) use ($product) {
                $product->categories()->attach($category->id);
            });
        });
    }
}
