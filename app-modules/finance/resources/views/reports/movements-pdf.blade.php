<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Informe de movimientos</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 4px;
        }

        .meta {
            color: #6b7280;
            font-size: 10px;
            margin-bottom: 16px;
        }

        .filters {
            margin: 0 0 16px;
            padding: 8px 10px;
            background-color: #f3f4f6;
            border-radius: 4px;
            font-size: 10px;
            color: #374151;
        }

        .totals {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .totals td {
            width: 33.33%;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
        }

        .totals .label {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .totals .value {
            display: block;
            font-size: 13px;
            font-weight: bold;
        }

        .totals .income .value {
            color: #059669;
        }

        .totals .expense .value {
            color: #dc2626;
        }

        table.movements {
            width: 100%;
            border-collapse: collapse;
        }

        table.movements th,
        table.movements td {
            border: 1px solid #e5e7eb;
            padding: 5px 6px;
            text-align: left;
            font-size: 10px;
        }

        table.movements th {
            background-color: #f3f4f6;
            text-transform: uppercase;
            font-size: 9px;
            color: #374151;
        }

        table.movements td.amount {
            text-align: right;
            white-space: nowrap;
        }

        .empty {
            padding: 20px;
            text-align: center;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>Informe de movimientos</h1>
    <p class="meta">Generado el {{ $generatedAt->format('d/m/Y H:i') }} — {{ $movements->count() }} {{ $movements->count() === 1 ? 'movimiento' : 'movimientos' }}</p>

    @if (filled($filters))
        <div class="filters">
            <strong>Filtros aplicados:</strong> {{ implode(' · ', $filters) }}
        </div>
    @endif

    <table class="totals">
        <tr>
            <td class="income">
                <span class="label">Ingresos</span>
                <span class="value">{{ $totals['income'] }}</span>
            </td>
            <td class="expense">
                <span class="label">Gastos</span>
                <span class="value">{{ $totals['expense'] }}</span>
            </td>
            <td>
                <span class="label">Neto</span>
                <span class="value">{{ $totals['net'] }}</span>
            </td>
        </tr>
    </table>

    @if ($movements->isEmpty())
        <p class="empty">No hay movimientos con los filtros aplicados.</p>
    @else
        <table class="movements">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Cuenta(s)</th>
                    <th>Categoría</th>
                    <th>Contexto</th>
                    <th>Monto (COP)</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movements as $movement)
                    <tr>
                        <td>{{ $movement->type->label() }}</td>
                        <td>{{ $movement->accountsLabel() }}</td>
                        <td>{{ $movement->category?->name ?? '—' }}</td>
                        <td>{{ $movement->financialContext?->name ?? '—' }}</td>
                        <td class="amount">{{ $movement->formattedAmount() }}</td>
                        <td>{{ $movement->date->format('d/m/Y') }}</td>
                        <td>{{ $movement->description ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
