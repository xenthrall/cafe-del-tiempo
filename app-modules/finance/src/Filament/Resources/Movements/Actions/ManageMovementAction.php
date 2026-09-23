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
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Filament\Traits\BuildsMovementFormOptions;
use Tequia\Finance\Models\Account;
use Tequia\Finance\Models\Movement;
use Tequia\Finance\Models\MovementTemplate;
use Tequia\Finance\Support\Money;

/**
 * Acción reutilizable para crear o editar un movimiento (ver docs/finance.md —
 * Interfaz Filament). Dos formas de indicar qué movimiento editar, para poder
 * usarse tanto suelta (blade, con `->arguments(['movement' => $id])`) como
 * `recordAction` de una Table de Filament (que enlaza el registro directo):
 * sin ninguna de las dos, crea un movimiento nuevo — a menos que llegue
 * `arguments(['template' => $id])` (ver `ManageMovementTemplates`), en cuyo
 * caso el formulario se precarga desde esa `MovementTemplate`. Delega el
 * guardado a `SaveMovement`, única puerta de entrada de la lógica de negocio,
 * para que ningún formulario pueda dejar datos inconsistentes.
 */
class ManageMovementAction extends Action
{
    use BuildsMovementFormOptions;

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
            // Panel deslizable en vez de modal centrado: en móvil, un modal
            // centrado queda apretado contra el teclado (solo le queda la
            // mitad de la pantalla). El slide-over ocupa el alto real del
            // dispositivo (h-dvh) y ancla el header/footer, así los botones
            // de guardar quedan siempre alcanzables sin scrollear.
            ->slideOver()
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
                    ->options(fn (Get $get): Collection => $this->accountOptions($get('from_account_id')))
                    ->visible(fn (Get $get): bool => $get('type') === MovementType::Transfer->value)
                    ->required(fn (Get $get): bool => $get('type') === MovementType::Transfer->value)
                    ->different('to_account_id')
                    ->live()
                    ->native(false),

                // Solo ofrece cuentas de la misma moneda que la de origen —
                // una transferencia no convierte moneda (ver SaveMovement,
                // que además la rechaza en el guardado si igual llegara un
                // par inválido).
                Select::make('to_account_id')
                    ->label('Cuenta de destino')
                    ->options(fn (Get $get): Collection => $this->accountOptions($get('to_account_id'), $this->accountCurrency($get('from_account_id'))))
                    ->visible(fn (Get $get): bool => $get('type') === MovementType::Transfer->value)
                    ->required(fn (Get $get): bool => $get('type') === MovementType::Transfer->value)
                    ->native(false),
            ])->visible(fn (Get $get): bool => $get('type') === MovementType::Transfer->value),

            Select::make('account_id')
                ->label('Cuenta')
                ->options(fn (Get $get): Collection => $this->accountOptions($get('account_id')))
                ->visible(fn (Get $get): bool => $get('type') !== MovementType::Transfer->value)
                ->required(fn (Get $get): bool => $get('type') !== MovementType::Transfer->value)
                ->native(false),

            Grid::make(2)->schema([
                // Antes que la categoría a propósito: las categorías ahora
                // pertenecen a un contexto (ver docs/finance.md), así que
                // elegir el contexto primero acota las opciones de categoría.
                Select::make('financial_context_id')
                    ->label('Contexto financiero (opcional)')
                    ->options(fn (Get $get): Collection => $this->contextOptions($get('financial_context_id')))
                    ->live()
                    ->native(false),

                Select::make('category_id')
                    ->label('Categoría (opcional)')
                    ->options(fn (Get $get): Collection => $this->categoryOptions($get('type'), $get('financial_context_id'), $get('category_id')))
                    ->visible(fn (Get $get): bool => in_array($get('type'), [
                        MovementType::Income->value,
                        MovementType::Expense->value,
                    ], true))
                    // Buscable: un usuario con muchas categorías/subcategorías
                    // por contexto no debería tener que scrollear la lista
                    // para encontrar la suya.
                    ->searchable()
                    ->native(false),
            ]),

            Grid::make(2)->schema([
                TextInput::make('amount')
                    ->label('Monto')
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->live(onBlur: false)
                    ->helperText(fn (Get $get): ?string => $get('type') === MovementType::Adjustment->value
                        ? 'Usa un valor negativo para reducir el saldo.'
                        : null)
                    ->belowContent(fn (Get $get): array => $this->amountPreview($get)),

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
     * Previsualización en vivo del monto (formato en la moneda de la cuenta
     * elegida + escrito en palabras si es COP), para que un cero de más al
     * digitar montos grandes ("1.500.000" vs "15.000.000") se note antes de
     * guardar en vez de después.
     *
     * @return array<int, Text>
     */
    private function amountPreview(Get $get): array
    {
        $amount = $get('amount');

        if (! is_numeric($amount)) {
            return [];
        }

        $value = (float) $amount;
        $currency = $this->selectedCurrency($get);

        $preview = [
            Text::make(Money::format($value, $currency))
                ->weight(FontWeight::SemiBold)
                ->size(TextSize::Small),
        ];

        // El deletreo es un texto en español pensado para pesos colombianos
        // ("mil quinientos pesos colombianos"); no tiene sentido traducirlo
        // por moneda, así que solo se muestra para COP.
        if ($currency === 'COP') {
            $preview[] = Text::make($this->spellOutAmount($value))
                ->size(TextSize::ExtraSmall)
                ->color('gray');
        }

        return $preview;
    }

    /**
     * Moneda de la cuenta relevante según el tipo de movimiento: `account_id`
     * para ingreso/gasto/ajuste, `from_account_id` para una transferencia
     * (origen y destino comparten moneda, ver SaveMovement).
     */
    private function selectedCurrency(Get $get): string
    {
        $accountId = $get('type') === MovementType::Transfer->value
            ? $get('from_account_id')
            : $get('account_id');

        return $this->accountCurrency($accountId) ?? 'COP';
    }

    private function accountCurrency(mixed $accountId): ?string
    {
        if (! $accountId) {
            return null;
        }

        return Account::query()->whereKey($accountId)->value('currency');
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
            // Viene de "Registrar" en Frecuentes (ver ManageMovementTemplates):
            // precarga todo el formulario desde la plantilla, el usuario solo
            // confirma (o ajusta) la fecha y guarda.
            if ($templateId = $arguments['template'] ?? null) {
                return [
                    ...MovementTemplate::findOrFail($templateId)->toMovementFormData(),
                    'date' => now()->toDateString(),
                ];
            }

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
}
