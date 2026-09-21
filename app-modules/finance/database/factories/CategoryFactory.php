<?php

namespace Tequia\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Models\Category;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'type' => fake()->randomElement(CategoryType::cases()),
            'parent_id' => null,
        ];
    }
}
