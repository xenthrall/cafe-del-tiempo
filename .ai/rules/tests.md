---
paths:
  - 'app-modules/*/tests/**'
---

# Tests

## Module Pest tests must be bound from the root tests/Pest.php, not a module-local one
Pest only auto-discovers a `Pest.php` inside the `tests/` directory tree. A `Pest.php` placed at `app-modules/{module}/tests/Pest.php` is silently never loaded — tests there fall back to Pest's default (non-Laravel) TestCase, and any Eloquent/DB usage fails with obscure errors like "Call to a member function connection() on null" instead of a clear "TestCase not bound" error.

Fix: bind module test directories from the root `tests/Pest.php` using a relative glob, e.g.:

```php
pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('../app-modules/*/tests/Feature');
```

Also: `internachi/modular` model factories don't auto-resolve because models live outside `App\Models`. Each model needs an explicit `protected static function newFactory()` override, and each factory needs an explicit `protected $model = X::class;` property — the `@extends Factory<X>` PHPDoc alone is not enough at runtime.

Also: this app's `App\Providers\AppServiceProvider` does not bind `Faker\Generator::class`, so `$this->faker` inside factories throws "Unknown format" errors. Use the `fake()` helper instead.

Remember to run `composer update {vendor}/{module}` after adding a new module to composer.json's require — just listing it there and running `composer install`/`update` on other packages does not register its autoload/provider until that module is explicitly updated.
