---
name: livewire-flux-table-package
description: Use this skill when the project needs to create, modify, fix, document, or integrate dynamic tables using the Livewire Flux UI tables package. Activate it for tasks related to column definitions, filters, global search, sticky columns, custom cells, pagination, mobile-first responsive behavior, controller integration, and Blade rendering. Do not use it for plain Livewire tables as the main entrypoint, or to reimplement the package from scratch when a reusable abstraction already exists.
---

# Livewire Flux Table Package Skill

## Purpose

This skill defines how to professionally use the dynamic tables package for Laravel with a **server-driven** architecture, **Blade + Flux UI** renderer, state based on **Request / query string**, and support for:

- declarative columns
- basic and extensible filters
- global search
- sorting
- pagination
- sticky columns
- custom Blade cells
- mobile-first behavior
- reuse via dedicated table classes or inline builders

The goal is for the agent to **use the package correctly**, maintain a consistent API, and avoid ad hoc solutions that break the system's reusability.

---

## When to use this skill

Use this skill when the user asks for any of the following:

- creating a new admin or dashboard table using the package
- migrating a manual HTML table to the package
- adding filters, search, sorting, or pagination to an existing table
- fixing columns as sticky left or right
- creating custom cells with Blade views
- integrating a table from a Laravel controller
- making a table reusable across multiple modules
- improving the responsive/mobile behavior of an existing table
- fixing bugs in the package or extending it without breaking its architecture
- writing documentation, examples, or tests for the package

Do not use this skill when:

- the user explicitly wants a plain Livewire table and has decided not to use the package
- the task is a simple, static list that needs no filters, search, or reuse
- the best solution is a one-off component with no intention of reuse

---

## Mandatory principles

1. **Server-driven first**
   - Data is built from the controller or a dedicated table class.
   - The view only renders the table; it does not contain complex business logic or query logic.

2. **Do not couple to Livewire as the main entrypoint**
   - Livewire may be a future or optional integration.
   - The base implementation must work with Laravel + Blade + Request + query string.

3. **Headless logic + visual renderer**
   - Keep column definitions, filters, state, query, and rendering separate.
   - Avoid putting query rules, complex transformations, or heavy branching in Blade.

4. **Reusability over local speed**
   - If a table can live as a dedicated class, prefer the dedicated class.
   - Use an inline builder only when the case is truly one-off.

5. **Real mobile-first**
   - It is not enough to "just display". The table must remain usable with many columns and touch input.
   - Consider hiding secondary columns, compacting the toolbar, and allowing controlled horizontal scroll.

6. **URL-shareable state**
   - Persist search, filters, sort, direction, page, and perPage in the query string.
   - Do not implement behavior that breaks shareable URLs without a strong reason.

---

## Agent workflow

### 1. Inspect context before writing code

Before implementing:

- locate the package and its real namespace
- review how the table is currently instantiated
- identify whether the project uses a dedicated class, inline builder, or both
- detect available Blade components from the package
- check existing filters, naming conventions, and visual style
- verify whether the project uses Eloquent, Query Builder, Collection, or a resolved paginator

If the package does not yet exist, build the solution respecting the architecture defined by the project.

### 2. Choose the right pattern

Use a **dedicated table class** when:

- the table is reused
- there are multiple columns or filters
- there is stable configuration logic
- the table belongs to an important admin module

Use an **inline builder** when:

- the listing is small or one-off
- a dedicated class is not worth creating
- the context is temporary or very localized

### 3. Keep a declarative API

When adding columns or filters, use a clear and predictable API. Prefer expressions like:

```php
Column::make('Name', 'name')->searchable()->sortable()->sticky()
SelectFilter::make('Role', 'role')->options([...])
DateRangeFilter::make('Period', 'created_at')
```

Avoid hard-to-read patterns or undocumented magic APIs.

### 4. Render from Blade without polluting the view

The view should stay simple, for example:

```blade
<livewire:tables.users.users-table />
```

If complex cells are needed, encapsulate them in a custom Blade cell view or well-defined formatters.

### 5. Verify the full experience

Every change must verify:

- desktop render
- small screen render
- correct query string
- pagination
- filters
- sorting
- sticky column behavior
- custom cells

---

## Recommended usage patterns

### Option A — Dedicated table class

Use this pattern by default for important modules.

```php
use App\Models\User;
use HubSoluciones\LivewireFluxTables\Columns\Column;
use HubSoluciones\LivewireFluxTables\Filters\SelectFilter;
use HubSoluciones\LivewireFluxTables\Filters\DateRangeFilter;
use HubSoluciones\LivewireFluxTables\Filters\TextFilter;
use HubSoluciones\LivewireFluxTables\Livewire\FluxTableComponent;

class UsersTable extends FluxTableComponent
{
    public function builder()
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')->searchable()->sortable()->sticky(),
            Column::make('Email', 'email')->searchable()->sortable(),
            Column::make('Role', 'role')->sortable(),
            Column::make('Status', 'status')->view('tables.cells.user-status'),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('Name', 'name'),
            SelectFilter::make('Role', 'role')->options([
                'admin' => 'Admin',
                'user' => 'User',
            ]),
            DateRangeFilter::make('Period', 'created_at'),
        ];
    }
}
```

Blade:

```blade
<livewire:tables.users.users-table />
```

### Option B — Inline builder

Use this pattern only when a dedicated class is not warranted.

```php
use HubSoluciones\LivewireFluxTables\Columns\Column;
use HubSoluciones\LivewireFluxTables\Filters\TextFilter;
use HubSoluciones\LivewireFluxTables\Filters\DateRangeFilter;
use HubSoluciones\LivewireFluxTables\Livewire\FluxTableComponent;

class UsersTable extends FluxTableComponent
{
    public function builder()
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')->searchable()->sortable()->sticky(),
            Column::make('Email', 'email')->searchable()->sortable(),
            Column::make('Created', 'created_at')->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('Name', 'name'),
            DateRangeFilter::make('Period', 'created_at'),
        ];
    }
}
```

---

## Artisan commands

The package provides two commands for scaffolding table components. Always prefer these over creating files manually.

### `livewire-flux-tables:make`

Generates a Livewire table component with fine-grained control over what gets created.

```bash
php artisan livewire-flux-tables:make {name} [options]
```

| Option | Description |
|--------|-------------|
| `--model=` | Eloquent model to bind (basename or full class). Sets `builder()` to `Model::query()` |
| `--path=` | Nested path under `app/Livewire` (e.g. `Tables/Users`) |
| `--view` | Generate a dedicated Blade view for the component |
| `--view-path=` | Nested path under `resources/views` for the Blade view |
| `--with-filters` | Include starter filter declarations in `filters()` |
| `--with-filter-methods` | Include starter `applyXFilter()` methods in the component |
| `--with-cell-views` | Include a sample custom cell Blade view |
| `--with-selection` | Include a `SelectionColumn` with a starter bulk action method |
| `--paginate=15` | Default `perPage` value (defaults to 15) |
| `--force` | Overwrite existing files |
| `--stub=default` | Stub set name (for custom published stubs) |

**Examples:**

```bash
# Minimal — just the component class
php artisan livewire-flux-tables:make UsersTable

# With model binding and placed in a subfolder
php artisan livewire-flux-tables:make UsersTable --model=User --path=Tables/Users

# Full: class + view + filters + filter methods + cell view
php artisan livewire-flux-tables:make UsersTable --model=User --path=Tables/Users --view --view-path=livewire/tables/users --with-filters --with-filter-methods --with-cell-views
```

**Generated files (full example):**
- `app/Livewire/Tables/Users/UsersTable.php`
- `resources/views/livewire/tables/users/users-table.blade.php`
- `resources/views/livewire/tables/users/cells/status-badge.blade.php`

---

### `livewire-flux-tables:scaffold`

Alias of `make` with all generation flags pre-enabled (`--view`, `--with-filters`, `--with-filter-methods`, `--with-cell-views`). Use it when you want everything generated in one shot.

```bash
php artisan livewire-flux-tables:scaffold {name} [options]
```

Accepts the same options as `make`, minus the feature toggles (they are always on).

```bash
php artisan livewire-flux-tables:scaffold UsersTable --model=User --path=Tables/Users --view-path=livewire/tables/users
```

---

### Publishing package assets

```bash
# Publish config
php artisan vendor:publish --tag=livewire-flux-tables-config

# Publish Blade views (to customize rendering)
php artisan vendor:publish --tag=livewire-flux-tables-views

# Publish stubs (to customize generated code)
php artisan vendor:publish --tag=livewire-flux-tables-stubs

# Publish translation files (to add or customize locales)
php artisan vendor:publish --tag=livewire-flux-tables-lang

# Publish the Claude Code skill into a consumer project
php artisan vendor:publish --tag=livewire-flux-tables-skill
```

Once stubs are published they live in `stubs/livewire-flux-tables/` and are picked up automatically by `make` and `scaffold`.

Translation files are published to `lang/vendor/livewire-flux-tables/`. The skill is published to `.claude/skills/livewire-flux-table-package/`.

---

### When to use each command

| Scenario | Command |
|----------|---------|
| Need full control over what gets generated | `make` with specific flags |
| Want everything generated at once | `scaffold` |
| Creating a simple table without filters | `make --model=Model` |
| Creating a complete admin module table | `scaffold --model=Model --path=... --view-path=...` |

---

## Rules by feature

### Columns

When adding columns:

- define `label` and `field` clearly
- use `searchable()` only when it makes real sense
- use `sortable()` only if the data source can resolve it correctly
- use `sticky('left'|'right')` only on priority columns
- set `width()` when readability requires it
- use `mobileHidden()` for secondary columns
- use `default()` when the value may be empty or null

### Sticky columns

When working with sticky columns:

- keep sticky columns to a minimum, typically 1 to 2
- ensure `background`, `z-index`, and borders are preserved
- avoid layouts where too many sticky columns kill usable space
- verify real horizontal scroll on both mobile and desktop
- if sticky worsens mobile UX, disable it or adapt behavior at small breakpoints

### Filters

Implement filters with these rules:

- each filter must have a stable, semantic `key`
- all state reading and writing must come from the Request / query string
- changing filters or search must reset `page`
- date filters must use a consistent format
- `DateRangeFilter` must clearly map `from` and `to`
- `SelectFilter` must accept clean, predictable options

### Global search

Global search:

- must only operate on columns marked as `searchable()`
- must not search indiscriminately across all fields
- must coexist with filters and sorting
- must have a configurable placeholder
- must persist in the URL

### Sorting

When implementing sort:

- respect `sort` and `direction` from the query string
- use only `asc` / `desc`
- support sort via callback when the field is not direct
- define a sensible default sort when the context requires it
- do not enable visual sort if the backend cannot resolve it correctly

### Pagination

Pagination must:

- support `paginate()` or `simplePaginate()` as appropriate
- allow `perPage` control
- preserve state in the query string
- reset the page when search or filters change
- render visually with the package's style

### Custom cells

For special cells:

- prefer `view()` when there is complex UI or structure
- use `format()` only for simple transformations
- the cell view must receive enough context: `$row`, `$value`, `$column`, `$component`
- avoid heavy logic or queries inside the cell

### Translations

The package ships with English (`en`) and Spanish (`es`) locales. When generating or modifying tables:

- Never hardcode user-facing strings in Spanish directly in table classes or views — use `__()` so they respect the active locale.
- The active locale is `config('livewire-flux-tables.locale')` when set, or `app()->getLocale()` when `null` (the default).
- Individual strings (`search_placeholder`, `empty_state_heading`, `empty_state_message`) can be overridden in the config without touching translation files.
- Column and filter labels (e.g. `Column::make('Name', 'name')`) are developer-supplied. Wrap them in `__('Name')` if translation is required.
- To add a new locale, the user publishes `--tag=livewire-flux-tables-lang` and creates the corresponding JSON file.

### Mobile-first

Every new or modified table must review:

- adaptive toolbar
- usable horizontal scroll
- reasonable touch targets
- hideable secondary columns on mobile
- compact or stacked mode if the package supports it

Do not accept a table as "responsive" if on mobile it only shrinks text until it breaks readability.

---

## Selection column (bulk actions)

Use `SelectionColumn::make()` as the **first element** of `columns()` to enable row selection, a tri-state header dropdown, and a bulk-actions toolbar. This is the only way to activate selection — do not override `hasBulkCheckboxes()` or `bulkActions()` (those hooks were removed).

```php
use HubSoluciones\LivewireFluxTables\Columns\SelectionColumn;

public function columns(): array
{
    return [
        SelectionColumn::make()->bulkActions([
            'deleteSelected' => 'Delete selected',
            'exportSelected' => 'Export selected',
        ]),
        Column::make('Name', 'name')->sortable(),
        // ...
    ];
}

public function deleteSelected(): void
{
    // When selectAllRecords is true, allFilteredKeys() returns ALL IDs matching
    // current search + filters (without pagination). Use it for "select all" bulk ops.
    $ids = $this->selectAllRecords ? $this->allFilteredKeys() : $this->selectedKeys;
    User::whereIn('id', $ids)->delete();
    $this->clearSelection();
}
```

**Header UX**: clicking the header checkbox opens a dropdown with "Select page (N)", "Select all M records" (only if total > page), and "Clear selection".

**Defaults** (all overridable via chaining):
- `sticky('left')` — use `->notSticky()` to disable
- `width('3rem')`, `align('center')`
- `hideable(false)` — never appears in the Columns dropdown

**Key public helpers on the component**:
- `$selectedKeys` — array of selected row keys (strings)
- `$selectAllRecords` — bool flag meaning "all filtered records are conceptually selected"
- `allFilteredKeys(): array` — all IDs matching active search + filters (no pagination)
- `clearSelection()` — resets both `selectedKeys` and `selectAllRecords`
- `selectionColumn(): ?SelectionColumn` — returns the declared column or null
- `hasSelection(): bool` — whether a `SelectionColumn` is present

**Conditional selectability**: use `->selectableWhen(fn ($row, $component) => $row->status !== 'locked')` to render certain rows' checkboxes as `disabled`.

---

## What the agent must avoid

Do not do this:

- turn the Blade view into the place where the query is assembled
- add unnecessary JavaScript when Laravel / Blade / Flux already solves the case
- reinvent a manual table if the package already covers the requirement
- add Livewire as a central dependency without the user asking for it
- mix filter, sort, and render responsibilities into one giant class
- break URL state persistence
- use sticky columns indiscriminately
- hide mobile UX bugs behind `overflow-x-auto` without verifying the real result
- override `hasBulkCheckboxes()` or `bulkActions()` — those hooks were removed; use `SelectionColumn::make()` instead

---

## Mandatory quality criteria

Every solution built with this package must meet the following:

1. **Clarity of use** — the table can be understood quickly from the controller or dedicated class.
2. **Consistency** — columns, filters, and state follow a uniform pattern.
3. **Reusability** — the solution works across more than one module without copy-pasting logic.
4. **Extensibility** — does not block future features like bulk actions, export, or per-user preferences.
5. **Real UX** — works well on both desktop and mobile.
6. **Maintainability** — new code does not require editing many layers for small changes.

---

## Definition of done

Consider the task complete only if:

- the table is built with the package, not with improvised HTML
- data arrives from the controller or table class
- the view correctly renders the package component
- search, filters, sort, and pagination work as expected
- the query string preserves state
- custom cells work without breaking the architecture
- mobile behavior was verified
- if the package was touched, examples, tests, or documentation were updated accordingly

---

## How to respond when using this skill

When executing tasks with this skill:

- briefly explain the architectural decision made
- state whether you will use a dedicated class or inline builder and why
- implement the change completely, not partially, unless the user limits the scope
- if you detect a prior bad decision, propose a concrete improvement
- keep examples aligned with the real namespace of the project

---

## Example prompts to invoke it

- Create a reusable users table with search, filters, and sticky columns using the package.
- Migrate this manual Blade listing to the Flux Tables package.
- Add a date range filter and a custom status cell to this table.
- Fix the mobile behavior of this package table without breaking desktop.
- Implement sort via callback for a relationship column using the package.
- Document how to use this table from a controller and Blade view in this project.

---

## Expected output

The agent must produce professional, consistent, and reusable solutions using the package as the official infrastructure for dynamic Livewire tables with Flux UI or Flux UI Pro if exist.
