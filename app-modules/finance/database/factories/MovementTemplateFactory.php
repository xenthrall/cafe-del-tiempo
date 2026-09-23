<?php

namespace Tequia\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\MovementTemplate;

/**
 * @extends Factory<MovementTemplate>
 */
class MovementTemplateFactory extends Factory
{
    protected $model = MovementTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => fake()->randomElement([MovementType::Income, MovementType::Expense]),
            'amount' => fake()->randomFloat(2, 1000, 500000),
            'is_active' => true,
        ];
    }
}
