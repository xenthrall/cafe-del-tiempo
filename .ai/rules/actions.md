---
paths:
  - 'app-modules/finance/src/Filament/Resources/*/Actions/**'
---

# Actions

## Custom Actions support both arguments-based and native record binding
A custom `Filament\Actions\Action` subclass meant to be reused both as a standalone action (invoked in Blade via `->arguments(['movement' => $id])`) and as a Table `recordAction` (Filament binds the record directly) should accept BOTH in its closures: declare `array $arguments, ?Model $record` and resolve the id with `$record?->id ?? ($arguments['key'] ?? null)`. Filament injects `null` for the nullable model param when no record is bound (e.g. a header "create" button), so this works for create/edit/delete without duplicating the action. See `Movements/Actions/ManageMovementAction.php` and `DeleteMovementAction.php`.

## Nested schema field closures don't receive the action's $arguments/$record
On a custom Action (see the `ManageMovementAction` dual-binding pattern already recorded), only the action's own top-level closures (`label()`, `modalHeading()`, `icon()`, `fillForm()`, `action()`) get `array $arguments` and `?Model $record` injected. A closure passed to `options()`/`visible()`/etc. on a field INSIDE `schema()` does NOT receive them — even `?Model $record` fails there, not just `$arguments` — and Filament throws "was unresolvable" (for `$arguments`) or silently resolves `$record` to null (no error, just wrong data — this is the dangerous one).

Fix: expose whatever the nested closure needs as real schema state instead:
- If it's data the record already carries (e.g. an FK a Select needs while editing), read the field's own pre-filled value via `Get` (`fillForm()` already populated it) rather than trying to inject `$record`.
- If it's data from `$arguments` that has no matching visible field (e.g. a parent scope like `financial_context_id` for a category form), add a `Filament\Forms\Components\Hidden` field for it, populate it in `fillForm()`, and read it via `Get` in the nested closure. Strip it from `$data` before persisting if it's not a real column.

See `ManageMovementAction::contextOptions()` (reads its own field via `Get`, not `$record`) and `FinancialContexts/Actions/ManageCategoryAction` (`Hidden::make('financial_context_id')`) for both variants. Caught only by mounting the action and inspecting `getOptions()` in tinker — `callAction()`/`fillForm()` alone won't surface it since the wrong value still passes as a technically-valid empty/partial options list until you compare against the expected content.
