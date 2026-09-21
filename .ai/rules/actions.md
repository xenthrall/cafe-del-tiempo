---
paths:
  - 'app-modules/finance/src/Filament/Resources/*/Actions/**'
---

# Actions

## Custom Actions support both arguments-based and native record binding
A custom `Filament\Actions\Action` subclass meant to be reused both as a standalone action (invoked in Blade via `->arguments(['movement' => $id])`) and as a Table `recordAction` (Filament binds the record directly) should accept BOTH in its closures: declare `array $arguments, ?Model $record` and resolve the id with `$record?->id ?? ($arguments['key'] ?? null)`. Filament injects `null` for the nullable model param when no record is bound (e.g. a header "create" button), so this works for create/edit/delete without duplicating the action. See `Movements/Actions/ManageMovementAction.php` and `DeleteMovementAction.php`.
