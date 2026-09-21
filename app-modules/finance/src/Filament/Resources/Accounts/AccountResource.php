<?php

namespace Tequia\Finance\Filament\Resources\Accounts;

use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Tequia\Finance\Filament\Resources\Accounts\Pages\ManageAccounts;
use Tequia\Finance\Models\Account;
use UnitEnum;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $navigationLabel = 'Cuentas';

    protected static string|UnitEnum|null $navigationGroup = 'Finanzas';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'cuenta';

    protected static ?string $pluralModelLabel = 'cuentas';

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageAccounts::route('/'),
        ];
    }
}
