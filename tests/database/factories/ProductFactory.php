<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Xoshbin\TranslatableSelect\Tests\Models\Category;
use Xoshbin\TranslatableSelect\Tests\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->words(3, true),
                'ku' => $this->faker->words(3, true),
                'ar' => $this->faker->words(3, true),
            ],
            'description' => [
                'en' => $this->faker->paragraph(),
                'ku' => $this->faker->paragraph(),
                'ar' => $this->faker->paragraph(),
            ],
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'category_id' => Category::factory(),
            'active' => $this->faker->boolean(90),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function withSpecificTranslations(array $translations): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $translations,
        ]);
    }
}
