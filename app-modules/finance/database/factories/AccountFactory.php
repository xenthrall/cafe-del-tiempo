<?php

namespace Tequia\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tequia\Finance\Enums\AccountType;
use Tequia\Finance\Models\Account;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => fake()->randomElement(AccountType::cases()),
            'currency' => 'COP',
            'opening_balance' => 0,
        ];
    }
}
