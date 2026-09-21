<?php

namespace Tequia\Finance\Filament\Resources\Movements\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use NumberFormatter;
use Tequia\Finance\Actions\SaveMovement;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Category;
use Tequia\Finance\Models\FinancialContext;
use Tequia\Finance\Models\Movement;
use Tequia\Finance\Support\Money;

/**
 * Acción reutilizable para crear o editar un movimiento (ver docs/finance.md —
 * Interfaz Filament). Dos formas de indicar qué movimiento editar, para poder
 * usarse tanto suelta (blade, con `->arguments(['movement' => $id])`) como
 * `recordAction` de una Table de Filament (que enlaza el registro directo):
 * sin ninguna de las dos, crea un movimiento nuevo. Delega el guardado a
 * `SaveMovement`, única puerta de entrada de la lógica de negocio, para que
 * ningún formulario pueda dejar datos inconsistentes.
 */
class ManageMovementAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manageMovement';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (array $arguments, ?Movement $record): string => $this->isEditing($arguments, $record) ? 'Editar movimiento' : 'Nuevo movimiento')
            ->modalHeading(fn (array $arguments, ?Movement $record): string => $this->isEditing($arguments, $record) ? 'Editar movimiento' : 'Nuevo movimiento')
            ->modalWidth(Width::Large)
            ->icon(fn (array $arguments, ?Movement $record) => $this->isEditing($arguments, $record) ? 'heroicon-o-pencil-square' : 'heroicon-o-plus')
            ->schema($this->formSchema())
            ->fillForm(fn (array $arguments, ?Movement $record): array => $this->fillFormData($arguments, $record))
            ->action(function (array $data, array $arguments, ?Movement $record, Action $action): void {
                $this->save($data, $arguments, $record, $action);
            });
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function isEditing(array $arguments, ?Movement $record): bool
    {
        return $this->resolveMovementId($arguments, $record) !== null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function resolveMovementId(array $arguments, ?Movement $record): ?int
    {
        return $record?->id ?? ($arguments['movement'] ?? null);
    }

    /**
     * @return array<int, Component>
     */
    private function formSchema(): array
    {
        return [
            Select::make('type')
                ->label('Tipo')
                ->options(collect(MovementType::cases())->mapWithKeys(
                    fn (MovementType $type): array => [$type->value => $type->label()],
                ))
                ->required()
                ->live()
                ->native(false),

            Grid::make(2)->schema([
                Select::make('from_account_id')
                    ->label('Cuenta de origen')
                    ->options(fn (): Collection => $this->accountOptions())
                    ->visible(fn (Get $get): bool => $get('type') === MovementType::Transfer->value)
                    ->required(fn (Get $get): bool => $get('type') === MovementType::Transfer->value)
                    ->different('to_account_id')
                    ->native(false),

                Select::make('to_account_id')
                    ->label('Cuenta de destino')
                    ->options(fn (): Collection => $this->accountOptions())
                    ->visible(fn (Get $get): bool => $get('type') === MovementType::Transfer->value)
                    ->required(fn (Get $get): bool => $get('type') === MovementType::Transfer->value)
                    ->native(false),
            ])->visible(fn (Get $get): bool => $get('type') === MovementType::Transfer->value),

            Select::make('account_id')
                ->label('Cuenta')
                ->options(fn (): Collection => $this->accountOptions())
                ->visible(fn (Get $get): bool => $get('type') !== MovementType::Transfer->value)
                ->required(fn (Get $get): bool => $get('type') !== MovementType::Transfer->value)
                ->native(false),

            Grid::make(2)->schema([
                Select::make('category_id')
                    ->label('Categoría (opcional)')
                    ->options(fn (Get $get): Collection => $this->categoryOptions($get('type')))
                    ->visible(fn (Get $get): bool => in_array($get('type'), [
                        MovementType::Income->value,
                        MovementType::Expense->value,
                    ], true))
                    ->native(false),

                Select::make('financial_context_id')
                    ->label('Contexto financiero (opcional)')
                    ->options(fn (): Collection => FinancialContext::query()->orderBy('name')->pluck('name', 'id'))
                    ->native(false),
            ]),

            Grid::make(2)->schema([
                TextInput::make('amount')
                    ->label('Monto (COP)')
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->live(onBlur: false)
                    ->helperText(fn (Get $get): ?string => $get('type') === MovementType::Adjustment->value
                        ? 'Usa un valor negativo para reducir el saldo.'
                        : null)
                    ->belowContent(fn (Get $get): array => $this->amountPreview($get('amount'))),

                DatePicker::make('date')
                    ->label('Fecha')
                    ->required(),
            ]),

            Textarea::make('description')
                ->label('Descripción (opcional)')
                ->rows(2),
        ];
    }

    /**
     * Previsualización en vivo del monto (formato COP + escrito en palabras),
     * para que un cero de más al digitar montos grandes ("1.500.000" vs
     * "15.000.000") se note antes de guardar en vez de después.
     *
     * @return array<int, Text>
     */
    private function amountPreview(mixed $amount): array
    {
        if (! is_numeric($amount)) {
            return [];
        }

        $value = (float) $amount;

        return [
            Text::make(Money::format($value))
                ->weight(FontWeight::SemiBold)
                ->size(TextSize::Small),
            Text::make($this->spellOutAmount($value))
                ->size(TextSize::ExtraSmall)
                ->color('gray'),
        ];
    }

    private function spellOutAmount(float $amount): string
    {
        $pesos = (int) $amount;

        $formatter = new NumberFormatter('es', NumberFormatter::SPELLOUT);
        $words = str_replace("\u{00AD}", '', $formatter->format($pesos));

        // Apocope: "veintiuno"/"...y uno" -> "veintiún"/"...y un" before a
        // noun ("un peso", never "uno peso"), the one SPELLOUT gets wrong.
        $words = match (true) {
            str_ends_with($words, 'veintiuno') => substr($words, 0, -2).'ún',
            str_ends_with($words, ' uno') => substr($words, 0, -3).'un',
            $words === 'uno' => 'un',
            default => $words,
        };

        $words = ucfirst($words);
        $unit = abs($pesos) === 1 ? 'peso colombiano' : 'pesos colombianos';

        return "{$words} {$unit}";
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function fillFormData(array $arguments, ?Movement $record): array
    {
        $movementId = $this->resolveMovementId($arguments, $record);

        if ($movementId === null) {
            return [
                'type' => $arguments['type'] ?? MovementType::Expense->value,
                'date' => now()->toDateString(),
            ];
        }

        $movement = $record ?? Movement::findOrFail($movementId);

        return [
            'type' => $movement->type->value,
            'account_id' => $movement->account_id,
            'from_account_id' => $movement->from_account_id,
            'to_account_id' => $movement->to_account_id,
            'category_id' => $movement->category_id,
            'financial_context_id' => $movement->financial_context_id,
            'amount' => (string) $movement->amount,
            'date' => $movement->date->toDateString(),
            'description' => $movement->description,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    private function save(array $data, array $arguments, ?Movement $record, Action $action): void
    {
        $movementId = $this->resolveMovementId($arguments, $record);

        try {
            if ($movementId !== null) {
                app(SaveMovement::class)->update($record ?? Movement::findOrFail($movementId), $data);
            } else {
                app(SaveMovement::class)->create($data);
            }
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('No se pudo guardar el movimiento')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();

            $action->halt();
        }
    }

    private function accountOptions(): Collection
    {
        return Account::query()->orderBy('name')->pluck('name', 'id');
    }

    /**
     * @return Collection<int|string, string>
     */
    private function categoryOptions(?string $type): Collection
    {
        if (! in_array($type, [CategoryType::Income->value, CategoryType::Expense->value], true)) {
            return collect();
        }

        // Not flatMap(): it collapses via array_merge and discards integer keys,
        // which are the option values a Select needs to stay bound to the id.
        $options = [];

        Category::query()
            ->where('type', $type)
            ->with(['children' => fn ($query) => $query->orderBy('name')])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->each(function (Category $category) use (&$options): void {
                $options[$category->id] = $category->name;

                foreach ($category->children as $child) {
                    $options[$child->id] = "{$category->name} > {$child->name}";
                }
            });

        return collect($options);
    }
}
