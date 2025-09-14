<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Xoshbin\TranslatableSelect\Tests\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        
        return [
            'name' => [
                'en' => $name,
                'ku' => $this->faker->words(2, true),
                'ar' => $this->faker->words(2, true),
            ],
            'description' => [
                'en' => $this->faker->sentence(),
                'ku' => $this->faker->sentence(),
                'ar' => $this->faker->sentence(),
            ],
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'active' => $this->faker->boolean(80),
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
