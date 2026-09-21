---
paths:
  - 'app-modules/finance/database/migrations/**'
---

# Migrations

## Protect referenced financial records with restrictOnDelete(), not cascade/null
`movements.account_id`/`from_account_id`/`to_account_id` and `movements.financial_context_id` use `restrictOnDelete()`, not `cascadeOnDelete()`/`nullOnDelete()` — a cascade or silent null would destroy real transaction history. `movements.category_id` and `categories.financial_context_id` stay `nullOnDelete()` on purpose (categories/contexts can be deleted freely; only accounts and contexts with real money movements against them are protected). Pair any such DB-level restrict with an app-level `hasMovements()`-style check on the model (see `Account`, `FinancialContext`) so the UI can warn before the DB throws.
