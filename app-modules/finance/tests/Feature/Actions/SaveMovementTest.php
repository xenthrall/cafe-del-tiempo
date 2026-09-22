<?php

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Tequia\Finance\Actions\SaveMovement;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\FinancialContext;
use Tequia\Finance\Models\Movement;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates an income movement scoped to a single account', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create(['type' => CategoryType::Income]);

    $movement = (new SaveMovement)->create([
        'type' => 'income',
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 100,
        'date' => '2026-01-10',
    ]);

    expect($movement->type)->toBe(MovementType::Income)
        ->and($movement->account_id)->toBe($account->id)
        ->and($movement->category_id)->toBe($category->id)
        ->and($movement->from_account_id)->toBeNull()
        ->and($movement->to_account_id)->toBeNull();
});

it('nulls the account and category fields for a transfer', function () {
    $source = Account::factory()->create();
    $destination = Account::factory()->create();

    $movement = (new SaveMovement)->create([
        'type' => 'transfer',
        'from_account_id' => $source->id,
        'to_account_id' => $destination->id,
        'amount' => 50,
        'date' => '2026-01-10',
    ]);

    expect($movement->account_id)->toBeNull()
        ->and($movement->category_id)->toBeNull()
        ->and($movement->from_account_id)->toBe($source->id)
        ->and($movement->to_account_id)->toBe($destination->id);
});

it('rejects a transfer between accounts of different currencies', function () {
    $source = Account::factory()->create(['currency' => 'COP']);
    $destination = Account::factory()->create(['currency' => 'USD']);

    (new SaveMovement)->create([
        'type' => 'transfer',
        'from_account_id' => $source->id,
        'to_account_id' => $destination->id,
        'amount' => 50,
        'date' => '2026-01-10',
    ]);
})->throws(ValidationException::class);

it('rejects a transfer to the same account', function () {
    $account = Account::factory()->create();

    (new SaveMovement)->create([
        'type' => 'transfer',
        'from_account_id' => $account->id,
        'to_account_id' => $account->id,
        'amount' => 50,
        'date' => '2026-01-10',
    ]);
})->throws(ValidationException::class);

it('nulls the from/to/category fields for an adjustment and allows a negative amount', function () {
    $account = Account::factory()->create();

    $movement = (new SaveMovement)->create([
        'type' => 'adjustment',
        'account_id' => $account->id,
        'amount' => -20,
        'date' => '2026-01-10',
    ]);

    expect($movement->amount)->toBe('-20.00')
        ->and($movement->from_account_id)->toBeNull()
        ->and($movement->to_account_id)->toBeNull()
        ->and($movement->category_id)->toBeNull();
});

it('rejects a zero-amount adjustment', function () {
    $account = Account::factory()->create();

    (new SaveMovement)->create([
        'type' => 'adjustment',
        'account_id' => $account->id,
        'amount' => 0,
        'date' => '2026-01-10',
    ]);
})->throws(ValidationException::class);

it('rejects a non-positive amount for an income movement', function () {
    $account = Account::factory()->create();

    (new SaveMovement)->create([
        'type' => 'income',
        'account_id' => $account->id,
        'amount' => 0,
        'date' => '2026-01-10',
    ]);
})->throws(ValidationException::class);

it('rejects a category whose type does not match the movement type', function () {
    $account = Account::factory()->create();
    $expenseCategory = Category::factory()->create(['type' => CategoryType::Expense]);

    (new SaveMovement)->create([
        'type' => 'income',
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 100,
        'date' => '2026-01-10',
    ]);
})->throws(ValidationException::class);

it('accepts an optional financial context', function () {
    $account = Account::factory()->create();
    $context = FinancialContext::factory()->create();

    $movement = (new SaveMovement)->create([
        'type' => 'expense',
        'account_id' => $account->id,
        'financial_context_id' => $context->id,
        'amount' => 30,
        'date' => '2026-01-10',
    ]);

    expect($movement->financial_context_id)->toBe($context->id);
});

it('updates an existing movement and re-applies the type rules', function () {
    $account = Account::factory()->create();
    $movement = Movement::factory()->expense()->create(['account_id' => $account->id, 'amount' => 10]);

    (new SaveMovement)->update($movement, [
        'type' => 'expense',
        'account_id' => $account->id,
        'amount' => 75,
        'date' => '2026-02-01',
    ]);

    expect($movement->fresh()->amount)->toBe('75.00');
});
