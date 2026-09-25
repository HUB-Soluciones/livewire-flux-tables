<?php

namespace HubSoluciones\LivewireFluxTables\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class SelectFilter extends Filter
{
    protected array $options = [];

    protected bool $searchable = false;

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function optionsList(): array
    {
        return $this->options;
    }

    /** Show the search input inside the `flux:select` listbox. */
    public function searchable(bool $value = true): static
    {
        $this->searchable = $value;

        return $this;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function type(): string
    {
        return 'select';
    }

    protected function applyDefault(mixed $data, mixed $value): mixed
    {
        if ($data instanceof EloquentBuilder || $data instanceof QueryBuilder) {
            $data->where($this->key(), $value);

            return $data;
        }

        if ($data instanceof Collection) {
            return $this->applyToCollection(
                $data,
                $value,
                fn ($row, $selected) => data_get($row, $this->key()) == $selected
            );
        }

        return $data;
    }
}
