<?php

namespace Tequia\Finance\Filament\Resources\Movements\Actions;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Tequia\Finance\Enums\MovementType;
use Tequia\Finance\Filament\Resources\Movements\Pages\ManageMovements;
use Tequia\Finance\Models\Movement;
use Tequia\Finance\Support\Money;

/**
 * Genera y descarga un informe (Excel o PDF) de los movimientos que la
 * página tiene filtrados en ese momento — reutiliza
 * `ManageMovements::filteredMovementsQuery()`/`activeFiltersSummary()`, así
 * que el informe siempre refleja exactamente lo que el usuario está viendo
 * en pantalla (tipo, periodo, contexto, categoría). Solo tiene sentido
 * usada desde `ManageMovements`.
 *
 * Excel: `openspout/openspout` (ya viene con `filament/actions`, no hace
 * falta una dependencia nueva). PDF: `barryvdh/laravel-dompdf`, agregado al
 * `composer.json` del módulo para esto — liviano en vez de un motor de
 * navegador headless.
 */
class GenerateMovementsReportAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'generateMovementsReport';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Generar informe')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->modalHeading('Generar informe de movimientos')
            ->modalDescription('El informe incluye los movimientos con los filtros que tienes aplicados ahora mismo.')
            ->modalSubmitActionLabel('Generar')
            ->schema([
                Select::make('format')
                    ->label('Formato')
                    ->options([
                        'excel' => 'Excel (.xlsx)',
                        'pdf' => 'PDF',
                    ])
                    ->required()
                    ->default('excel')
                    ->native(false),
            ])
            ->action(fn (array $data): Response => $this->generate($data['format']));
    }

    private function generate(string $format): Response
    {
        $page = $this->resolvePage();
        $movements = $page->filteredMovementsQuery()->get();

        return $format === 'pdf'
            ? $this->generatePdf($movements, $page->activeFiltersSummary())
            : $this->generateExcel($movements);
    }

    private function resolvePage(): ManageMovements
    {
        $page = $this->getLivewire();

        if (! $page instanceof ManageMovements) {
            throw new RuntimeException('GenerateMovementsReportAction solo puede usarse desde ManageMovements.');
        }

        return $page;
    }

    /**
     * @param  Collection<int, Movement>  $movements
     */
    private function generateExcel(Collection $movements): Response
    {
        $path = tempnam(sys_get_temp_dir(), 'movements-report').'.xlsx';

        $writer = new XlsxWriter;
        $writer->openToFile($path);

        $writer->addRow(Row::fromValues(
            ['Tipo', 'Cuenta(s)', 'Categoría', 'Contexto', 'Monto', 'Moneda', 'Fecha', 'Descripción'],
            (new Style)->setFontBold(),
        ));

        foreach ($movements as $movement) {
            $writer->addRow(Row::fromValues([
                $movement->type->label(),
                $movement->accountsLabel(),
                $movement->category?->name ?? '',
                $movement->financialContext?->name ?? '',
                (string) $movement->amount,
                $movement->currency(),
                $movement->date->format('d/m/Y'),
                (string) $movement->description,
            ]));
        }

        $writer->close();

        return response()->download($path, $this->filename('xlsx'))->deleteFileAfterSend(true);
    }

    /**
     * Livewire solo reconoce una descarga de archivo cuando la acción
     * devuelve un `StreamedResponse`/`BinaryFileResponse` (ver
     * `SupportFileDownloads::valueIsntAFileResponse()`) — el
     * `Illuminate\Http\Response` que da `Pdf::download()` directamente no
     * cuenta, así que Livewire intenta serializar el binario del PDF como
     * si fuera un valor de retorno normal y `json_encode()` truena con
     * "Malformed UTF-8 characters". Envolverlo en `streamDownload()` lo
     * convierte en un `StreamedResponse`, que sí dispara la descarga.
     *
     * @param  Collection<int, Movement>  $movements
     * @param  array<int, string>  $filters
     */
    private function generatePdf(Collection $movements, array $filters): Response
    {
        $pdf = Pdf::loadView('finance::reports.movements-pdf', [
            'movements' => $movements,
            'filters' => $filters,
            'generatedAt' => now(),
            'totals' => $this->totals($movements),
        ]);

        return response()->streamDownload(
            function () use ($pdf): void {
                echo $pdf->output();
            },
            $this->filename('pdf'),
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * Un total por moneda en vez de uno solo (ver docs/finance.md —
     * Multi-moneda): sin conversión automática, sumar movimientos de
     * monedas distintas como si fueran una sola sería un número sin
     * sentido. Con una sola moneda entre los movimientos filtrados (el
     * caso normal) el informe muestra un solo total, igual que antes.
     *
     * @param  Collection<int, Movement>  $movements
     * @return array<int, array{currency: string, income: string, expense: string, net: string}>
     */
    private function totals(Collection $movements): array
    {
        $income = [];
        $expense = [];

        foreach ($movements as $movement) {
            $currency = $movement->currency();

            match ($movement->type) {
                MovementType::Income => $income[$currency] = bcadd($income[$currency] ?? '0', (string) $movement->amount, 2),
                MovementType::Expense => $expense[$currency] = bcadd($expense[$currency] ?? '0', (string) $movement->amount, 2),
                default => null,
            };
        }

        $currencies = array_unique([...array_keys($income), ...array_keys($expense)]);
        sort($currencies);

        return collect($currencies)
            ->map(function (string $currency) use ($income, $expense): array {
                $currencyIncome = $income[$currency] ?? '0';
                $currencyExpense = $expense[$currency] ?? '0';

                return [
                    'currency' => $currency,
                    'income' => Money::format($currencyIncome, $currency),
                    'expense' => Money::format($currencyExpense, $currency),
                    'net' => Money::format(bcsub($currencyIncome, $currencyExpense, 2), $currency),
                ];
            })
            ->all();
    }

    private function filename(string $extension): string
    {
        return 'movimientos-'.now()->format('Y-m-d-His').'.'.$extension;
    }
}
