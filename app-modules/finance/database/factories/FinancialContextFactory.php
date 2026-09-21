<?php

namespace Tequia\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tequia\Finance\Models\FinancialContext;

/**
 * @extends Factory<FinancialContext>
 */
class FinancialContextFactory extends Factory
{
    protected $model = FinancialContext::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
        ];
    }
}
