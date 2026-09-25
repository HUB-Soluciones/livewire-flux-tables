# Changelog

## [0.4.0] - 2026-09-25

### Changed

- **Breaking:** `livewire/flux-pro` is now a required dependency (`composer.json` `require`, not `suggest`). The package renders every interactive control — text/select/date/date-range filters, the toolbar search, the "per page" select, and the mobile sort control — with Flux UI components, including Pro-only ones (`flux:select variant="listbox"`, `flux:date-picker`). This package targets internal use where a Flux Pro license is available.
- **Breaking:** removed the `flux_tier` config key and `FluxTableComponent::usesFluxPro()`. There is no more `base`/`auto`/`pro` detection or fallback — Pro is always used. If your app published `config/livewire-flux-tables.php`, remove the `flux_tier` line (it is ignored otherwise).
- **Breaking:** removed `resources/views/components/mobile-sort-pro.blade.php`. Its content now lives in `mobile-sort.blade.php`, which is the only mobile-sort view.
- The filters panel (`resources/views/components/filters.blade.php`) now renders `flux:field`/`flux:label`, `flux:select` (`SelectFilter`), `flux:date-picker` (`DateFilter`), and `flux:date-picker mode="range"` with presets (`DateRangeFilter`) instead of raw `<select>`/`<input>` markup.
- The toolbar search input and the "records per page" select are now `flux:input` and `flux:select` respectively, instead of raw HTML.

### Added

- `SelectFilter::searchable()` — shows the search field inside the `listbox` select variant.
- `DateRangeFilter::presets()` / `DateRangeFilter::withoutPresets()` — control the preset shortcuts (today, last 7 days, this month, …) shown in the range date picker. Presets are enabled by default.
- `FluxTableComponent::$tableDateRanges` bridges `flux:date-picker mode="range"` back into each `DateRangeFilter`'s existing `['from' => ..., 'to' => ...]` state — no change needed to `applyUsing()`, convention-based `apply{Key}Filter()` methods, or query-string persistence. Handles both a hydrated `Flux\DateRange` and the plain `['start' => ..., 'end' => ..., 'preset' => ...]` array the picker delivers over the wire (Livewire has no prior type metadata for a path that started out `null`, so `Flux\DateRangeSynth` isn't applied on the way back in).
- Column visibility (the "Columns" dropdown) is now remembered per table across page loads, stored in the session under a key derived from the Livewire component name.
- `Filter::width('sm'|'md'|'lg'|'full')` (aliases: `small()`, `medium()`, `large()`, `fullWidth()`) — controls how much of the filter panel's 12-column grid each filter occupies. Defaults to `md`, overridable globally via `config('livewire-flux-tables.filter_default_width')`.
- Filter controls (`flux:input`/`flux:select`/`flux:date-picker` in the filters panel) now render compact (`size="sm"`) by default instead of Flux's normal control height, addressing filters that looked overly tall and wide when the panel was open. Configurable via `config('livewire-flux-tables.filter_size')` or per table with `protected ?string $filtersSize = 'default';` to opt back into full-size controls.

### Fixed

- Unchecking a column in the "Columns" dropdown could leave its checkbox showing checked again after any later interaction with the table, even though the column stayed hidden. The dropdown built its own checkbox out of a plain `flux:checkbox` with a static `:checked` attribute and a separate `wire:click` on the menu item; `flux:checkbox` renders a custom `ui-checkbox` element that only reads its `checked` attribute once, on first connect, so it never picked up later state changes. Rebuilt the menu with Flux's `flux:menu.checkbox.group` / `flux:menu.checkbox`, which are `wire:model`-aware and stay in sync (`FluxTableComponent::$hiddenColumns` + `toggleColumn()` were replaced by `$visibleColumnFields`, bound directly to the group).
- The "records per page" select in the toolbar stretched to full width and wrapped onto its own row below the search bar instead of sitting next to "Columns". It's now wrapped with a fixed compact width.

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
