---
paths:
  - 'app-modules/finance/src/Filament/Resources/*/Tables/**'
---

# Tables

## Table config lives in its own Tables/*Table.php class, presentation only
A resource's Filament Table config goes in its own class (e.g. `Movements/Tables/MovementsTable.php`) with a static `configure(Table $table, Builder $query, ...): Table`, called from the page's `table()` method. This class only handles presentation — columns, `recordActions`, pagination — and takes an already-filtered `Builder` as a parameter. It does not build the base query or know about filter state.

Filtering (type tabs, date range/presets, any `Select`-based filter) is NOT done via `Filament\Tables\Filters\Filter` — it's page-owned: plain public properties on the page with their own Blade controls (`wire:model.live`), a private `filteredQuery(): Builder` method on the page that applies all active filters, and an `updatedX()` hook per filter property calling `$this->resetTable()` (not just `resetPage()` — see the trap below) so changing a filter doesn't strand the user on an empty page. See `Movements/Pages/ManageMovements` (`filteredMovementsQuery()`, `periodDateRange()`) and `Movements/Tables/MovementsTable::configure()`.

## Trap: a page property that `table()` reads needs `resetTable()`, not `resetPage()`, in its setter
`InteractsWithTable::bootedInteractsWithTable()` runs `$this->table = $this->table($this->makeTable())` during Livewire's `booted` phase, which fires *before* the request's action method (e.g. a `wire:click="setViewMode(...)"` handler) runs. So when our own `table()` override bakes page state (view mode, filters) straight into the `Table`/query at build time — unlike Filament's native filters, which store values separately and apply them later at record-fetch time, so they don't hit this — the table gets built with the *previous* value, one request behind. The fix: any setter/`updatedX()` hook for a property `table()` depends on must call `$this->resetTable()` (which re-runs `bootedInteractsWithTable()` synchronously, plus resets pagination) after mutating the property, not just `$this->resetPage()`. Symptom if forgotten: the UI looks like it needs "double-clicking" to take effect.

## Per-record custom Blade view instead of TextColumn for a "card" look
To get full control over a table row's responsive HTML (not achievable with `Split`/`Stack` layout columns), use `Filament\Tables\Columns\Layout\View::make('view.path')` as the *only* entry in `columns()`. Inside that Blade view, `$getRecord()` gives the model. `recordActions()` still render automatically at the row's end regardless of this — no need to embed them in the custom view. See `Movements/Tables/MovementsTable` (`cards` view mode) and `resources/views/.../tables/movement-card.blade.php`.

## Extract TextColumn sets into their own Tables/Columns/*Columns.php class
When a table mode uses plain `TextColumn`s, define them in a dedicated class with a static `make(): array`, imported by the `*Table.php` class — keeps the table class from being saturated with per-field column definitions. See `Movements/Tables/Columns/MovementTableColumns.php`.
