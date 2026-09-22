<?php

use Tequia\Finance\Support\Money;

it('formats an amount in Colombian pesos by default', function () {
    expect(Money::format(1500000.5))->toBe('$ 1.500.000,50')
        ->and(Money::format(-500))->toBe('-$ 500,00');
});

it('formats an amount in a currency other than COP', function () {
    expect(Money::format(1234.5, 'USD'))->toBe('US$ 1.234,50');
});
