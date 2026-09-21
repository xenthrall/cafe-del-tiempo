<?php

namespace Tequia\Finance\Enums;

enum AccountType: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case DigitalWallet = 'digital_wallet';
    case CreditCard = 'credit_card';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Bank => 'Cuenta bancaria',
            self::DigitalWallet => 'Billetera digital',
            self::CreditCard => 'Tarjeta de crédito',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash => 'heroicon-o-banknotes',
            self::Bank => 'heroicon-o-building-library',
            self::DigitalWallet => 'heroicon-o-device-phone-mobile',
            self::CreditCard => 'heroicon-o-credit-card',
        };
    }
}
