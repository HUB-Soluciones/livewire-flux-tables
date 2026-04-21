# livewire-flux-tables

A Laravel package for building reusable, Livewire-first data tables styled for Flux UI and Tailwind CSS.

## Requirements

- PHP 8.1+
- Laravel 10+
- Livewire 3.5.19+ or 4.0+
- Tailwind CSS 4.0+

## Installation

```bash
composer require hubsoluciones/livewire-flux-tables
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
```

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

| Class | Input | Default behavior |
|-------|-------|-----------------|
| `TextFilter` | Text input | `LIKE %value%` |
| `SelectFilter` | Dropdown | Exact match (`where field = value`) |
| `DateFilter` | Date picker | `whereDate field = value` |
| `DateRangeFilter` | Two date pickers (from/to) | `whereDate >=` and `whereDate <=` |

```php
use HubSoluciones\LivewireFluxTables\Filters\SelectFilter;
use HubSoluciones\LivewireFluxTables\Filters\DateRangeFilter;

public function filters(): array
{
    return [
        SelectFilter::make('Role', 'role')
            ->options(['admin' => 'Admin', 'user' => 'User'])
            ->placeholder('All roles'),

        DateRangeFilter::make('Created', 'created_at'),
    ];
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

## Configuration

After publishing, edit `config/livewire-flux-tables.php`:

```php
'default_per_page'   => 15,
'per_page_options'   => [10, 15, 25, 50, 100],
'persist_query_string' => true,       // sync state to URL query params
'search_placeholder' => 'Search...',
'default_sticky_width' => '12rem',
'table_wrapper_class' => '...',       // Tailwind classes for the outer wrapper
'table_scroll_class'  => 'overflow-x-auto',
'empty_state_heading' => 'No results',
'empty_state_message' => '...',
'pagination'          => 'length_aware', // or 'simple'
'stubs_path'          => 'stubs/livewire-flux-tables',
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

## Testing

```bash
composer test

# Single file
phpunit tests/Feature/FluxTableComponentTest.php

# Single method
phpunit tests/Feature/FluxTableComponentTest.php --filter test_global_search_filters_only_searchable_columns
```

## License

MIT
