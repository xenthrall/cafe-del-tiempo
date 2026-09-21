<?php

namespace Tequia\Finance\Support;

class Money
{
    /**
     * Formatea un monto para mostrarlo en la moneda de la cuenta. Por ahora
     * solo se opera en COP (ver docs/finance.md), pero la moneda es un
     * parámetro para no bloquear soporte a otras monedas más adelante.
     */
    public static function format(string|int|float $amount, string $currency = 'COP'): string
    {
        $value = (float) $amount;
        $formatted = number_format(abs($value), 2, ',', '.');
        $sign = $value < 0 ? '-' : '';
        $symbol = match ($currency) {
            'COP' => '$',
            default => $currency,
        };

        return "{$sign}{$symbol} {$formatted}";
    }
}
