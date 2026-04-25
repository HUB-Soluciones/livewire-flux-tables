# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

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
