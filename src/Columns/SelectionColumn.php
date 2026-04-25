<?php

namespace HubSoluciones\LivewireFluxTables\Columns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class SelectionColumn extends Column
{
    protected array $bulkActionsList = [];

    protected mixed $selectablePredicate = null;

    protected string $resourceSingular = 'record';

    protected string $resourcePlural = 'records';

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

    public function getNormalizedBulkActions(): array
    {
        $normalized = [];
        foreach ($this->bulkActionsList as $method => $config) {
            if (is_string($config)) {
                $normalized[$method] = ['label' => $config, 'icon' => null, 'variant' => null];
            } else {
                $normalized[$method] = [
                    'label'   => $config['label']   ?? $method,
                    'icon'    => $config['icon']    ?? null,
                    'variant' => $config['variant'] ?? null,
                ];
            }
        }

        return $normalized;
    }

    public function resource(string $singular, string $plural): static
    {
        $this->resourceSingular = $singular;
        $this->resourcePlural = $plural;

        return $this;
    }

    public function getResourceSingular(): string
    {
        return $this->resourceSingular;
    }

    public function getResourcePlural(): string
    {
        return $this->resourcePlural;
    }

    public function getResourceLabel(int $count): string
    {
        return $count === 1 ? $this->resourceSingular : $this->resourcePlural;
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
