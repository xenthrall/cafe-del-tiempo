<?php

use Tequia\Finance\Models\FinancialContext;
use Tequia\Finance\Models\Movement;

it('lists the movements registered under a financial context', function () {
    $context = FinancialContext::factory()->create(['name' => 'Vehículo Turbo']);
    Movement::factory()->create(['financial_context_id' => $context->id]);

    expect($context->fresh()->movements)->toHaveCount(1);
});

it('can exist without any movements', function () {
    $context = FinancialContext::factory()->create();

    expect($context->movements)->toBeEmpty();
});
