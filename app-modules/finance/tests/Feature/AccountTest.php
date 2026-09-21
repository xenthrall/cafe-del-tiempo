<?php

use Tequia\Finance\Enums\AccountType;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Movement;

it('casts type to an AccountType enum', function () {
    $account = Account::factory()->create(['type' => AccountType::Bank]);

    expect($account->fresh()->type)->toBe(AccountType::Bank);
});

it('starts the balance from the opening balance when there are no movements', function () {
    $account = Account::factory()->create(['opening_balance' => 100]);

    expect($account->balance())->toBe('100.00');
});

it('increases the balance by income movements', function () {
    $account = Account::factory()->create(['opening_balance' => 100]);
    Movement::factory()->income()->create(['account_id' => $account->id, 'amount' => 50]);

    expect($account->fresh()->balance())->toBe('150.00');
});

it('decreases the balance by expense movements', function () {
    $account = Account::factory()->create(['opening_balance' => 100]);
    Movement::factory()->expense()->create(['account_id' => $account->id, 'amount' => 30]);

    expect($account->fresh()->balance())->toBe('70.00');
});

it('applies a positive adjustment to the balance', function () {
    $account = Account::factory()->create(['opening_balance' => 100]);
    Movement::factory()->adjustment()->create(['account_id' => $account->id, 'amount' => 20]);

    expect($account->fresh()->balance())->toBe('120.00');
});

it('applies a negative adjustment to the balance', function () {
    $account = Account::factory()->create(['opening_balance' => 100]);
    Movement::factory()->adjustment()->create(['account_id' => $account->id, 'amount' => -20]);

    expect($account->fresh()->balance())->toBe('80.00');
});

it('decreases the balance of the source account for an outgoing transfer', function () {
    $source = Account::factory()->create(['opening_balance' => 100]);
    $destination = Account::factory()->create(['opening_balance' => 0]);
    Movement::factory()->transfer()->create([
        'from_account_id' => $source->id,
        'to_account_id' => $destination->id,
        'amount' => 25,
    ]);

    expect($source->fresh()->balance())->toBe('75.00');
});

it('increases the balance of the destination account for an incoming transfer', function () {
    $source = Account::factory()->create(['opening_balance' => 100]);
    $destination = Account::factory()->create(['opening_balance' => 0]);
    Movement::factory()->transfer()->create([
        'from_account_id' => $source->id,
        'to_account_id' => $destination->id,
        'amount' => 25,
    ]);

    expect($destination->fresh()->balance())->toBe('25.00');
});

it('does not count a transfer as a movement of either account', function () {
    $source = Account::factory()->create();
    $destination = Account::factory()->create();
    Movement::factory()->transfer()->create([
        'from_account_id' => $source->id,
        'to_account_id' => $destination->id,
    ]);

    expect($source->fresh()->movements)->toBeEmpty()
        ->and($destination->fresh()->movements)->toBeEmpty();
});
