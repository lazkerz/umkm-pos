# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Laravel 13 (PHP 8.3) server-rendered app (Blade + Alpine.js + Tailwind CSS via Vite) for **UMKM Kopi** — a multi-tenant POS + inventory + finance management system for coffee shop businesses with multiple branches ("toko"). Auth scaffolding is Laravel Breeze (Blade stack). Not a git repository (no `.git`) — do not assume `git log`/`git blame` are available.

## Common commands

```bash
# Full local setup (copies .env, generates key, migrates, installs & builds JS)
composer run setup

# Dev servers (Laravel serve + queue worker + pail logs + vite), all in one terminal
composer run dev

# Tests (clears config cache first, then runs php artisan test / PHPUnit)
composer run test
php artisan test --filter=SomeTestName   # single test
php artisan test tests/Feature/Auth/AuthenticationTest.php

# DB
php artisan migrate
php artisan migrate:fresh --seed   # UnitSeeder (global units) + DemoSeeder (demo owner/store/staff/data)
```

Demo login after seeding: `owner@umkmkopi.test` / `password` (owner), `kasir.medan@umkmkopi.test` / `password` (staff). DB is MySQL (`umkm_kopi`) in `.env`; tests run against in-memory SQLite (see `phpunit.xml`).

There are currently no feature tests for the domain logic (stock, transactions, reports) — `tests/` only has the default Breeze auth/profile tests.

## Architecture

### Tenancy model: Owner vs Staff, scoped by Store

Every `User` has a `role` of `owner` or `staff` (`app/Models/User.php`):
- **Owner** can own many `Store`s (`ownedStores()`, FK `stores.owner_id`) and gets an aggregate cross-store dashboard.
- **Staff** belongs to exactly one `Store` (`store_id` on the user) and only operates within it.

Almost every route lives under `stores/{store}/...` and is guarded by two middleware aliases registered in `bootstrap/app.php`:
- `owner` → `EnsureUserIsOwner` — role must be `owner` (used for store creation, staff management, stock distribution, expense approval, aggregate dashboard).
- `store.access` → `EnsureStoreAccess` — owner must own `{store}` (`owner_id`), staff must belong to it (`user.store_id === store.id`).

Because `{store}` and nested params (`{stockItem}`, `{product}`, etc.) use **plain, unscoped** route-model binding, every controller action touching a child resource re-checks tenancy manually with the repo-wide convention:
```php
abort_unless($childModel->store_id === $store->id, 404);
```
This is intentional and repeated across controllers (`CategoryController`, `ProductController`, `StockItemController`, `TransactionController`, `ExpenseController`, etc.) rather than centralized — keep using this pattern for new store-scoped resources instead of introducing scoped bindings or a trait.

Routes are defined flat (not `Route::resource`) in `routes/web.php`, split into an owner-only group (`prefix('owner')`) and a store-level group shared by both roles (`prefix('stores/{store}')`).

### Domain model

- `Store` — a branch, owned by a `User` (owner).
- `Category` / `Product` — menu, scoped per store. `Product` has `is_available` and a `price`.
- `Unit` — measurement units. Global defaults have `store_id = null` (seeded by `UnitSeeder`); stores can add custom ones. Selectable units for a store = `Unit::availableFor($storeId)` scope (global ∪ store's own).
- `StockItem` — raw-material inventory per store, with `quantity` and `minimum_stock` (low-stock alerts compare these). `quantity` is a running total that should **only** be mutated via `increment`/`decrement` alongside a `StockMovement` record — never edited directly through the "update" form (see `StockItemController::update()`, which intentionally excludes `quantity`).
- `StockMovement` — audit trail of every stock change, `type` ∈ `in|out|adjustment|distribution`.
- `StockDistribution` — Owner → Store stock transfers (`Owner\StockDistributionController`); also writes a `StockMovement` of type `distribution` and increments the `StockItem`.
- `ProductRecipe` — BOM: how much of a `StockItem` one unit of a `Product` consumes (`quantity_needed`).
- `Promotion` — percentage or fixed discount, scoped to a `channel` (`offline`/`online`/`both`) and an active date range (`isValidNow()`).
- `Expense` — has an approval workflow: staff-created expenses start `pending`, owner-created ones are auto-`approved` (see `ExpenseController::store()`); only `pending` expenses can be deleted; `Owner\ExpenseApprovalController` approves/rejects pending ones.
- `Transaction` / `TransactionItem` — a sale, `channel` ∈ `offline|online`, invoice numbers via `Transaction::generateInvoiceNumber()` (`INV-{storeId}-{Ymd}-{seq}`).

### Core transactional flow: `Store\TransactionController::store()`

This is the most business-critical path — a POS checkout that, in one `DB::transaction`:
1. Prices each line from `Product`, sums a subtotal.
2. Aggregates raw-material needs across all lines via each product's `recipes` (so two menu items sharing an ingredient combine into one deduction).
3. `lockForUpdate()`s the relevant `StockItem` rows and throws a `ValidationException` if any ingredient is insufficient — **before** writing anything — to prevent overselling under concurrent checkouts.
4. Applies a `Promotion` discount if one is passed and valid for the channel/date.
5. Creates the `Transaction` + `TransactionItem`s, then decrements stock and writes `StockMovement` (`type: out`) rows per ingredient.

Any change to checkout, recipes, or stock deduction should preserve the lock-then-validate-then-write ordering above.

### Reports

`Store\ReportController` (per-store) and `Owner\ReportController` (aggregate) compute "Laba Rugi" (P&L) as completed-transaction revenue minus approved-expense amount over a period, and export via `barryvdh/laravel-dompdf` (PDF) and `maatwebsite/excel` (`app/Exports/*`). Both controllers share a `resolvePeriod()`-style helper supporting `today|this_week|this_month|last_month|this_year|custom` presets — mirror that pattern rather than reimplementing period parsing if you add another report.

`Owner\DashboardController` aggregates across all of an owner's stores: Laba Rugi, low-stock alerts, store performance ranking, customer counts, active promos.
