<?php

namespace HubSoluciones\LivewireFluxTables\Columns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class SelectionColumn extends Column
{
    protected array $bulkActionsList = [];

    protected mixed $selectablePredicate = null;

    public function __construct()
    {
        parent::__construct('', '__selection__');

        $this->width = '3rem';
        $this->align = 'center';
        $this->fixedPosition = 'left';
        $this->hideable = false;
        $this->mobileHidden = false;
    }

    public static function make(string $label = '', ?string $field = null): static
    {
        return new static();
    }

    public function isSelectionColumn(): bool
    {
        return true;
    }

    public function bulkActions(array $actions): static
    {
        $this->bulkActionsList = $actions;

        return $this;
    }

    public function getBulkActions(): array
    {
        return $this->bulkActionsList;
    }

    public function selectableWhen(callable $predicate): static
    {
        $this->selectablePredicate = $predicate;

        return $this;
    }

    public function isRowSelectable(mixed $row, object $component): bool
    {
        if ($this->selectablePredicate === null) {
            return true;
        }

        return (bool) call_user_func($this->selectablePredicate, $row, $component);
    }

    public function notSticky(): static
    {
        $this->fixedPosition = null;

        return $this;
    }

    public function applySearch(EloquentBuilder|QueryBuilder $query, string $term): void
    {
        // No-op: selection columns are not searchable.
    }

    public function applySort(EloquentBuilder|QueryBuilder $query, string $direction): void
    {
        // No-op: selection columns are not sortable.
    }
}
