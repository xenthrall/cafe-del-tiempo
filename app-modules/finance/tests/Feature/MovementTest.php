<?php

use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\FinancialContext;
use Tequia\Finance\Models\Movement;

it('casts type to a MovementType enum', function () {
    $movement = Movement::factory()->income()->create();

    expect($movement->fresh()->type)->toBe(MovementType::Income);
});

it('casts amount to a decimal', function () {
    $movement = Movement::factory()->create(['amount' => 42.5]);

    expect($movement->fresh()->amount)->toBe('42.50');
});

it('casts date to a date', function () {
    $movement = Movement::factory()->create(['date' => '2026-01-15']);

    expect($movement->fresh()->date->toDateString())->toBe('2026-01-15');
});

it('belongs to the account it affects', function () {
    $account = Account::factory()->create();
    $movement = Movement::factory()->expense()->create(['account_id' => $account->id]);

    expect($movement->account)->toBeInstanceOf(Account::class)
        ->and($movement->account->id)->toBe($account->id);
});

it('belongs to a from and to account for a transfer', function () {
    $source = Account::factory()->create();
    $destination = Account::factory()->create();
    $movement = Movement::factory()->transfer()->create([
        'from_account_id' => $source->id,
        'to_account_id' => $destination->id,
    ]);

    expect($movement->fromAccount->id)->toBe($source->id)
        ->and($movement->toAccount->id)->toBe($destination->id)
        ->and($movement->account)->toBeNull();
});

it('can belong to a category', function () {
    $category = Category::factory()->create();
    $movement = Movement::factory()->expense()->create(['category_id' => $category->id]);

    expect($movement->category->id)->toBe($category->id);
});

it('can exist without a category', function () {
    $movement = Movement::factory()->transfer()->create(['category_id' => null]);

    expect($movement->category)->toBeNull();
});

it('can belong to a financial context', function () {
    $context = FinancialContext::factory()->create();
    $movement = Movement::factory()->create(['financial_context_id' => $context->id]);

    expect($movement->financialContext->id)->toBe($context->id);
});
