<?php

namespace Tequia\Finance\Enums;

enum MovementType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Ingreso',
            self::Expense => 'Gasto',
            self::Transfer => 'Transferencia',
            self::Adjustment => 'Ajuste',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Income => 'success',
            self::Expense => 'danger',
            self::Transfer => 'info',
            self::Adjustment => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Income => 'heroicon-o-arrow-trending-up',
            self::Expense => 'heroicon-o-arrow-trending-down',
            self::Transfer => 'heroicon-o-arrows-right-left',
            self::Adjustment => 'heroicon-o-adjustments-horizontal',
        };
    }
}
