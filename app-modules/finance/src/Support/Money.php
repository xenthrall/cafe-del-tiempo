<?php

namespace Tequia\Finance\Support;

use NumberFormatter;

class Money
{
    /**
     * Formatea un monto en la moneda de la cuenta (ver Currency::cases() en
     * docs/finance.md — Multi-moneda). Usa el formateador de moneda de intl
     * en vez de un mapeo manual de símbolos: da el símbolo/código correcto
     * para cualquier ISO 4217, no solo COP, con agrupación de miles y
     * decimales en español (coherente con el resto de la UI).
     */
    public static function format(string|int|float $amount, string $currency = 'COP'): string
    {
        $formatter = new NumberFormatter('es_CO', NumberFormatter::CURRENCY);

        // intl separa el símbolo del número con un espacio de no separación
        // (U+00A0); se normaliza a un espacio normal para no cambiar el
        // output visible de golpe en toda la UI/informes existentes.
        return str_replace("\u{00A0}", ' ', $formatter->formatCurrency((float) $amount, $currency));
    }
}
