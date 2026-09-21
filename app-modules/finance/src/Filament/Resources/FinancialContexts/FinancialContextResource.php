<?php

namespace Tequia\Finance\Filament\Resources\FinancialContexts;

use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Tequia\Finance\Filament\Resources\FinancialContexts\Pages\ManageFinancialContexts;
use Tequia\Finance\Models\FinancialContext;
use UnitEnum;

class FinancialContextResource extends Resource
{
    protected static ?string $model = FinancialContext::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Contextos';

    protected static string|UnitEnum|null $navigationGroup = 'Finanzas';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'contexto financiero';

    protected static ?string $pluralModelLabel = 'contextos financieros';

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageFinancialContexts::route('/'),
        ];
    }
}
