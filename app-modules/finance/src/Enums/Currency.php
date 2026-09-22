<?php

namespace Tequia\Finance\Enums;

enum Currency: string
{
    case Cop = 'COP';
    case Usd = 'USD';
    case Eur = 'EUR';
    case Mxn = 'MXN';
    case Ars = 'ARS';
    case Brl = 'BRL';

    public function label(): string
    {
        return match ($this) {
            self::Cop => 'Peso colombiano (COP)',
            self::Usd => 'Dólar estadounidense (USD)',
            self::Eur => 'Euro (EUR)',
            self::Mxn => 'Peso mexicano (MXN)',
            self::Ars => 'Peso argentino (ARS)',
            self::Brl => 'Real brasileño (BRL)',
        };
    }
}
