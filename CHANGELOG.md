# Changelog

## [0.3.2] - 2026-09-08

### Changed

- Mobile card fields (secondary summary rows and expanded detail rows) now stack the label above the value instead of placing them side by side. The previous side-by-side grid still squeezed the value into a fixed-width column, which caused long text (e.g. a full name) to wrap into a narrow, cramped block; stacking gives the value the full card width to wrap naturally.

## [0.3.1] - 2026-09-08

### Fixed

- The first and last columns of the desktop table no longer touch the wrapper card's edges. Flux's `flux:table.column`/`flux:table.cell` zero out horizontal padding on the first/last `th`/`td` (`first:ps-0 last:pe-0`), which assumes a borderless, edge-to-edge table; since the package wraps the table in a bordered, rounded card, that left the leading cell (checkbox or first data column) and the trailing cell pressed against the border. A new `table_edge_padding_class` config key restores 1rem of inset on both edges, matching the toolbar and pagination cards.

## [0.3.0] - 2026-09-01

### Added

- Livewire 4 is now the minimum supported version and Flux UI 2 is a required dependency.
- Flux Pro is detected through `Flux::pro()` with `auto`, `base`, and `pro` configuration modes.
- Multiple sticky columns on either side with deterministic cumulative offsets and edge shadows.
- Mobile card layout with explicit `mobileSummary()`, progressive disclosure, keyboard-accessible controls, and validated mobile sorting.
- Livewire 4 computed paginator caching and loading-state feedback.
- CI coverage for PHP 8.2–8.4 and an optional Flux Pro job enabled only when license secrets are present.

### Fixed

- Flux tables now always render with the free `flux:table.*` components. Flux Pro is used only
  for the optional mobile sort `listbox`; base installations use the native select fallback.


All notable changes to this project will be documented in this file.

## [0.2.0] - 2026-04-25

### Breaking Changes

- Removed `FluxTableComponent::hasBulkCheckboxes()` and `FluxTableComponent::bulkActions()`. Row selection is now declarative via `SelectionColumn::make()`.

#### Migration guide

**Before:**
```php
class UsersTable extends FluxTableComponent
{
    protected function hasBulkCheckboxes(): bool { return true; }
    protected function bulkActions(): array { return ['deleteSelected' => 'Delete']; }

    public function columns(): array
    {
        return [Column::make('Name', 'name')];
    }

    public function deleteSelected(): void
    {
        User::whereIn('id', $this->selectedKeys)->delete();
        $this->clearSelection();
    }
}
```

**After:**
```php
use HubSoluciones\LivewireFluxTables\Columns\SelectionColumn;

class UsersTable extends FluxTableComponent
{
    public function columns(): array
    {
        return [
            SelectionColumn::make()->bulkActions(['deleteSelected' => 'Delete']),
            Column::make('Name', 'name'),
        ];
    }

    public function deleteSelected(): void
    {
        $ids = $this->selectAllRecords ? $this->allFilteredKeys() : $this->selectedKeys;
        User::whereIn('id', $ids)->delete();
        $this->clearSelection();
    }
}
```

### Added

- `SelectionColumn` (`src/Columns/SelectionColumn.php`) — declarative selection column with sticky-left, tri-state header dropdown, and per-row conditional selectability via `selectableWhen(callable)`.
- Header dropdown with "Select page (N)", "Select all M records", and "Clear selection" options.
- `FluxTableComponent::hasSelection(): bool` — whether a `SelectionColumn` is declared.
- `FluxTableComponent::selectionColumn(): ?SelectionColumn` — returns the declared selection column.
- `FluxTableComponent::pageSelectionState(): string` — returns `'none'`, `'partial'`, or `'full'` for the tri-state checkbox.
- `FluxTableComponent::isRowSelected(mixed $row): bool` — helper for the view layer.
- `FluxTableComponent::allFilteredKeys(): array` — all primary key values matching the current search + filters, without pagination.
- `FluxTableComponent::totalRecords(): ?int` — total record count from the paginator.
- `FluxTableComponent::resolveRowKeyField(): string` — public accessor for `rowKeyField()`.
- `QueryPipeline::keys()` — extracts all keys from a data source after applying search, filters, and sort, without paginating.
- `Column::isSelectionColumn(): bool` — returns `false` on `Column`, overridden to `true` in `SelectionColumn`.
- Blade partials `selection-header.blade.php` and `selection-cell.blade.php`.
- i18n keys: `Select page (:count)`, `Select records` (added to `en.json` and `es.json`).
- `--with-selection` flag for `livewire-flux-tables:make` and `livewire-flux-tables:scaffold`.
- Fix: `TestCase` now sets `view.compiled` so tests work without a pre-created cache directory.
- Zebra striping support: optional per-component `protected ?bool $striped` property and global `zebra_striping` config key. New `FluxTableComponent::isStriped(): bool` and `FluxTableComponent::rowBackgroundClass(int $iteration, bool $isSelected): string`.
- Full dark mode across all table views (Tailwind `dark:` classes on wrapper, headers, rows, sort indicators, selection banner, sticky cells). Config keys `table_wrapper_class` and `sticky_header_class` include dark variants out of the box.
- Configurable selection banner: `selection_banner_class`, `selection_banner_text_class`, `selection_banner_link_class` in config.
- New config keys: `zebra_odd_class`, `zebra_even_class`, `row_base_class`, `sticky_header_class`.

### Fixed

- Sticky cells now inherit the correct row background (zebra color, selected state, or base) to avoid transparent bleed on horizontal scroll. Implemented via `is_sticky` flag in `StickyColumnManager` metadata, consumed in `table.blade.php`.
