<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Xoshbin\TranslatableSelect\Tests\Models\Tag;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->word(),
                'ku' => $this->faker->word(),
                'ar' => $this->faker->word(),
            ],
            'color' => $this->faker->hexColor(),
        ];
    }

    public function withSpecificTranslations(array $translations): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $translations,
        ]);
    }
}
