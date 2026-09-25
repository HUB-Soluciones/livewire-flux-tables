# livewire-flux-tables

A Laravel package for building reusable, Livewire-first data tables styled for Flux UI and Tailwind CSS.

## Requirements

- PHP 8.1+
- Laravel 10+
- Livewire 4.0+
- Flux UI 2.0+ (`livewire/flux`)
- **Flux UI Pro 2.0+ (`livewire/flux-pro`) — required, not optional.**
- Tailwind CSS 4.2+

> Every interactive control this package renders — filters, selects, date pickers, the
> mobile sort control — is a Flux UI component, and several of them (`flux:select
> variant="listbox"`, `flux:date-picker`) only exist in **Flux UI Pro**. This package is
> built for internal use where a Flux Pro license is available, so Pro is a hard
> `composer.json` requirement, not an optional upgrade.

## Installation

Flux Pro is distributed through a private Composer repository and requires an active
license. Configure your credentials before installing:

```bash
composer config repositories.flux-pro composer https://composer.fluxui.dev
composer config http-basic.composer.fluxui.dev "<your-flux-username>" "<your-license-key>"

composer require hubsoluciones/livewire-flux-tables
```

Installing the package pulls in both `livewire/flux` and `livewire/flux-pro` (`^2.0`)
automatically. Without valid Pro credentials configured first, `composer require` will fail.

Include Flux's assets in your application layout:

```blade
@fluxAppearance
@livewireStyles
@livewireScripts
@fluxScripts
```

Import Flux's stylesheet and register the package views as a Tailwind source in
`resources/css/app.css`:

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@source '../../vendor/hubsoluciones/livewire-flux-tables/resources/views/**/*.blade.php';

@custom-variant dark (&:where(.dark, .dark *));
```

Optionally publish the config, views, or stubs:

```bash
php artisan vendor:publish --tag=livewire-flux-tables-config
php artisan vendor:publish --tag=livewire-flux-tables-views
php artisan vendor:publish --tag=livewire-flux-tables-stubs
```

## Claude Code Skill

If you use [Claude Code](https://claude.ai/code), you can publish the skill for this package into your project so Claude understands how to work with `livewire-flux-tables` correctly:

```bash
php artisan vendor:publish --tag=livewire-flux-tables-skill
```

This copies `.claude/skills/laravel-flux-table-package/SKILL.md` into your project root. Claude Code picks it up automatically and uses it to guide column definitions, filters, sticky columns, custom cells, Artisan commands, and more.

To update the skill after upgrading the package:

```bash
php artisan vendor:publish --tag=livewire-flux-tables-skill --force
```

## Quick Start

Generate a table component using the Artisan command:

```bash
php artisan livewire-flux-tables:make UsersTable --model=User
```

Or scaffold one with a view and filter methods:

```bash
php artisan livewire-flux-tables:scaffold UsersTable --model=User --path=Tables/Users --view-path=livewire/tables/users --view --with-filters --with-filter-methods
```

Then render it in any Blade view:

```blade
<livewire:tables.users.users-table />
```

## Creating a Table

Extend `FluxTableComponent` and implement `columns()` plus one data source method:

```php
use App\Models\User;
use HubSoluciones\LivewireFluxTables\Columns\Column;
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
            Column::make('ID', 'id')->sortable(),
            Column::make('Name', 'name')->searchable()->sortable()->sticky()->width('14rem'),
            Column::make('Email', 'email')->searchable()->sortable(),
            Column::make('Role', 'role')->sortable(),
        ];
    }
}
```

### Data Sources

The component auto-detects the data source method in this order:

| Method | Type |
|--------|------|
| `builder()` | Eloquent Builder |
| `query()` | Query Builder (`DB::table(...)`) |
| `records()` | Collection or array |

All three support search, filtering, sorting, and pagination — SQL operations are used for builders, in-memory PHP for collections.

### Default Sort

```php
protected function defaultSort(): ?string
{
    return 'created_at';
}

protected function defaultSortDirection(): string
{
    return 'desc';
}
```

## Columns

```php
Column::make('Label', 'field_name')
    ->sortable()                        // enable sorting
    ->searchable()                      // include in global search
    ->sticky('left')                    // fixed column (left or right)
    ->width('14rem')                    // set column width
    ->align('center')                   // text alignment
    ->format(fn ($value, $row) => ...) // format the displayed value
    ->view('my-package::cell-view')     // use a custom Blade view for the cell
    ->html()                            // render value as raw HTML
    ->default('—')                      // fallback when value is null/empty
    ->mobileHidden()                    // hide on small screens
    ->mobileLabel('Alt label')          // override label on mobile
    ->stackOnMobile()                   // stack cell vertically on mobile
    ->mobileSummary()                   // keep in the always-visible mobile card summary
```

On small screens tables render as accessible, single-open cards by default. The first two
visible columns are used as the summary when no column is marked with `mobileSummary()`;
`stackOnMobile()` remains supported as a legacy summary hint. Set `mobile_layout` to `table`
to retain the horizontal table on mobile, or override `protected ?string $mobileLayout` on a
component.

Any number of columns can be fixed on either side. Sticky offsets are calculated from the
declared widths, so adjacent fixed columns do not overlap:

```php
Column::make('Name', 'name')->sticky('left')->width('14rem')->mobileSummary(),
Column::make('Status', 'status')->sticky('left')->width('8rem')->mobileSummary(),
Column::make('Actions', 'actions')->sticky('right')->width('7rem'),
```

The mobile card toolbar validates sort fields through `setSortField()` and uses Flux buttons
for direction changes. Interactive controls use Livewire 4's automatic `data-loading` state,
and the row
query is a `#[Computed]` property so the paginator is reused during a request.

Tables can also use Livewire 4's deferred loading without extra component code:

```blade
<livewire:users-table defer />
{{-- Or load only when the table enters the viewport: --}}
<livewire:users-table lazy />
```

Both modes display the package's accessible table skeleton while data loads.

### Custom Sort or Search Logic

```php
Column::make('Full Name', 'name')
    ->searchUsing(fn ($query, $term) => $query->orWhere('first_name', 'like', "%$term%")
                                               ->orWhere('last_name', 'like', "%$term%"))
    ->sortableUsing(fn ($query, $direction) => $query->orderBy('last_name', $direction));
```

### Relationship Fields

Dot-notation fields automatically use `whereHas` for search:

```php
Column::make('Team', 'team.name')->searchable()->sortable(),
```

### Custom Cell View

The view receives `$row`, `$value`, `$column`, and `$component`:

```blade
{{-- resources/views/cells/status.blade.php --}}
<span class="badge">{{ $value }}</span>
```

```php
Column::make('Status', 'status')->view('cells.status'),
```

## Filters

### Available Filter Types

Every filter renders as a Flux UI Pro component — no raw `<select>` or `<input type="date">`
anywhere in the panel.

| Class | Rendered as | Default behavior |
|-------|-------------|-----------------|
| `TextFilter` | `flux:input` | `LIKE %value%` |
| `SelectFilter` | `flux:select variant="listbox"` | Exact match (`where field = value`) |
| `DateFilter` | `flux:date-picker` | `whereDate field = value` |
| `DateRangeFilter` | `flux:date-picker mode="range"` (with presets) | `whereDate >=` and `whereDate <=` |

```php
use HubSoluciones\LivewireFluxTables\Filters\SelectFilter;
use HubSoluciones\LivewireFluxTables\Filters\DateRangeFilter;

public function filters(): array
{
    return [
        SelectFilter::make('Role', 'role')
            ->options(['admin' => 'Admin', 'user' => 'User'])
            ->placeholder('All roles')
            ->searchable(), // show the search input inside the listbox

        DateRangeFilter::make('Created', 'created_at')
            // Restrict (or reorder) the presets column — accepts a space-separated
            // string or an array of Flux\DateRangePreset values.
            ->presets(['today', 'yesterday', 'last7Days', 'thisMonth', 'yearToDate']),
            // ->withoutPresets(), // disable the presets column entirely
    ];
}
```

`DateRangeFilter` shows the presets column by default (Flux's own defaults: today,
yesterday, this week, last 7 days, this month, year to date, all time). Whichever preset the
user picks, or a manually chosen start/end, is mirrored back into the filter's `from`/`to`
state automatically — `applyUsing()`, a convention-based `apply{Key}Filter()` method, and
`chipValueLabel()` all keep working against the same `['from' => ..., 'to' => ...]` shape as
before.

### Filter Panel Layout

Each filter renders on a 12-column grid and picks its own width with `->width()`:

```php
TextFilter::make('Name', 'name')->width('md'),      // default
SelectFilter::make('Role', 'role')->width('sm'),    // narrow — short option lists
DateRangeFilter::make('Period', 'created_between')->width('lg'), // wide — range pickers
// ->width('full') to always take the whole row
```

| Width | Behavior |
|-------|----------|
| `sm` | Narrowest column — good for short selects/dates. |
| `md` | Default width. |
| `lg` | Half the row on desktop, full row on tablet. |
| `full` | Always spans the entire row. |

No `->width()` call falls back to `config('livewire-flux-tables.filter_default_width')` (`md`
by default).

Filter controls also render **compact by default** (Flux `size="sm"`) so the panel doesn't take
up excessive vertical space. Change it globally via `filter_size` in the config file, or per
table:

```php
class UsersTable extends FluxTableComponent
{
    // 'sm' (default) | 'default' — 'default' uses Flux's normal control height.
    protected ?string $filtersSize = 'default';
}
```

### Custom Filter Logic

Three ways to customize how a filter applies, in order of priority:

**1. Inline callback:**

```php
SelectFilter::make('Active', 'is_active')
    ->applyUsing(fn ($query, $value) => $query->where('active', (bool) $value));
```

**2. Component method (convention-based):**

```php
// Filter key: 'created_at' → method: applyCreatedAtFilter
public function applyCreatedAtFilter($query, $value): void
{
    $query->whereYear('created_at', $value);
}
```

**3. Default behavior** defined in the filter class itself.

## Selection & Bulk Actions

Add `SelectionColumn::make()` as the first column to enable row selection. The package automatically renders a **neutral selection banner** above the table with a row counter, "Select all / Clear selection" controls, and **inline bulk action buttons** — no extra wrapper Blade needed.

```blade
{{-- This is all you need in your view --}}
<livewire:tables.socios.socios-tabla />
```

```php
use HubSoluciones\LivewireFluxTables\Columns\SelectionColumn;

public function columns(): array
{
    return [
        SelectionColumn::make()
            ->resource('socio', 'socios')       // singular / plural for banner copy
            ->bulkActions([
                // string form — backwards-compatible
                'markInactive' => 'Mark inactive',

                // array form — adds icon and variant
                'export' => [
                    'label'   => 'Export selected',
                    'icon'    => 'arrow-down-tray', // any flux:icon name
                    'variant' => 'primary',          // 'primary' | 'danger' | 'default'
                ],
            ]),
        Column::make('Name', 'name')->sortable(),
        // ...
    ];
}

public function export(): void
{
    // $selectAllRecords=true means all filtered records are selected (not just the page).
    // Use allFilteredKeys() to get all matching IDs without pagination.
    $ids = $this->selectAllRecords ? $this->allFilteredKeys() : $this->selectedKeys;
    // act on $ids...
    $this->clearSelection();
}
```

The header checkbox opens a dropdown with "Select page (N)", "Select all M records", and "Clear selection". Rows can be conditionally disabled with `->selectableWhen(fn ($row) => ...)`.

**Defaults** (overridable): sticky-left, width 4.25rem, centered, not hideable. Use `->notSticky()` to remove the sticky behavior.

**Customize banner colors** (no need to publish the view):

```php
// config/livewire-flux-tables.php
'selection_banner_class'      => '...', // outer wrapper — default: neutral card matching the toolbar
'selection_banner_text_class' => '...', // counter text — default: text-zinc-700/zinc-200
'selection_banner_link_class' => '...', // links — default: zinc underlined on hover
```

**Generate with:** `php artisan livewire-flux-tables:make MyTable --with-selection`

## Configuration

After publishing, edit `config/livewire-flux-tables.php`:

```php
'default_per_page'   => 15,
'per_page_options'   => [10, 15, 25, 50, 100],
'persist_query_string' => true,       // sync state to URL query params
'search_placeholder' => 'Search...',
'default_sticky_width' => '12rem',
'mobile_layout'      => 'cards', // cards | table
'table_wrapper_class' => '...',       // Tailwind classes for the outer wrapper
'table_edge_padding_class' => '...',  // horizontal inset on the first/last th/td, restoring the
                                       // padding Flux zeroes out at the edges (`first:ps-0 last:pe-0`)
                                       // for borderless tables. If you override it, make sure the
                                       // classes are covered by your app's Tailwind @source/safelist.
'table_scroll_class'  => 'overflow-x-auto',
'empty_state_heading' => 'No results',
'empty_state_message' => '...',
'pagination'          => 'length_aware',  // or 'simple'
'stubs_path'          => 'stubs/livewire-flux-tables',
// Selection banner (neutral by default — override without publishing the view):
'selection_banner_class'      => '...',   // outer wrapper classes
'selection_banner_text_class' => '...',   // counter text classes
'selection_banner_link_class' => '...',   // "Select all" / "Clear selection" link classes
```

## Query String Persistence

When `persist_query_string` is enabled (default), all table state (search, sort, filters, page, perPage) is automatically synced to the URL. Each filter gets its own query parameter using the filter key as the alias.

To disable for a specific table, override in your component:

```php
protected function usesQueryStringPersistence(): bool
{
    return false;
}
```

## Translations

The package ships with English and Spanish translations. By default it uses your Laravel app's locale (`app()->getLocale()`).

**Publish the language files** to customize or add new locales:

```bash
php artisan vendor:publish --tag=livewire-flux-tables-lang
```

Files are published to `lang/vendor/livewire-flux-tables/`.

**Force a specific locale** regardless of the app locale:

```php
// config/livewire-flux-tables.php
'locale' => 'es', // 'en', 'es', or null (default — uses app locale)
```

**Add a new locale** by creating `lang/vendor/livewire-flux-tables/{locale}.json` using the English keys:

```json
{
    "Search records...": "Rechercher...",
    "Search": "Rechercher",
    "Records per page": "Enregistrements par page",
    "All": "Tous",
    "No results": "Aucun résultat",
    "No records match the current criteria.": "Aucun enregistrement ne correspond aux critères actuels."
}
```

**Override individual strings** without touching translation files — set the value directly in the config:

```php
'search_placeholder' => 'Type to filter...',
'empty_state_heading' => 'Nothing here',
'empty_state_message' => 'Try adjusting your filters.',
```

> Column labels and filter labels (e.g. `Column::make('Name', 'name')`) are developer-supplied. Pass `__('Name')` directly if you want them to be translatable.

## Testing

```bash
composer test

# Single file
phpunit tests/Feature/FluxTableComponentTest.php

# Single method
phpunit tests/Feature/FluxTableComponentTest.php --filter test_global_search_filters_only_searchable_columns
```

Mobile browser coverage uses Playwright and Chromium:

```bash
npm install
npm run test:e2e:install
npm run test:e2e
```

Run `npm run test:e2e:ui` to inspect and debug the scenario in Playwright's interactive browser UI. The test generates a full-page mobile screenshot at `test-results/mobile-table-complete.png`.

## License

MIT
