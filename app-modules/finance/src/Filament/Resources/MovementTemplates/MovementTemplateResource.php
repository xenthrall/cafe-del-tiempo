<?php

namespace Tequia\Finance\Filament\Resources\MovementTemplates;

use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Tequia\Finance\Filament\Resources\MovementTemplates\Pages\ManageMovementTemplates;
use Tequia\Finance\Models\MovementTemplate;
use UnitEnum;

class MovementTemplateResource extends Resource
{
    protected static ?string $model = MovementTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $navigationLabel = 'Frecuentes';

    protected static string|UnitEnum|null $navigationGroup = 'Finanzas';

    protected static ?int $navigationSort = 25;

    protected static ?string $modelLabel = 'movimiento frecuente';

    protected static ?string $pluralModelLabel = 'movimientos frecuentes';

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageMovementTemplates::route('/'),
        ];
    }
}
