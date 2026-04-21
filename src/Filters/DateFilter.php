<?php

namespace HubSoluciones\LivewireFluxTables\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class DateFilter extends Filter
{
    public function type(): string
    {
        return 'date';
    }

    protected function applyDefault(mixed $data, mixed $value): mixed
    {
        if ($data instanceof EloquentBuilder || $data instanceof QueryBuilder) {
            $data->whereDate($this->key(), $value);

            return $data;
        }

        if ($data instanceof Collection) {
            return $this->applyToCollection(
                $data,
                $value,
                fn ($row, $selected) => substr((string) data_get($row, $this->key(), ''), 0, 10) === $selected
            );
        }

        return $data;
    }
}
