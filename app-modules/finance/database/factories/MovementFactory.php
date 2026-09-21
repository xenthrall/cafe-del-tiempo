<?php

namespace Tequia\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Movement;

/**
 * @extends Factory<Movement>
 */
class MovementFactory extends Factory
{
    protected $model = Movement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => MovementType::Expense,
            'account_id' => Account::factory(),
            'from_account_id' => null,
            'to_account_id' => null,
            'category_id' => null,
            'financial_context_id' => null,
            'amount' => fake()->randomFloat(2, 1, 1000),
            'date' => fake()->date(),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * @return $this
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MovementType::Income,
        ]);
    }

    /**
     * @return $this
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MovementType::Expense,
        ]);
    }

    /**
     * @return $this
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MovementType::Transfer,
            'account_id' => null,
            'from_account_id' => Account::factory(),
            'to_account_id' => Account::factory(),
        ]);
    }

    /**
     * @return $this
     */
    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MovementType::Adjustment,
        ]);
    }
}
