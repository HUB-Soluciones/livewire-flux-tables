<?php

namespace HubSoluciones\LivewireFluxTables\Query;

use HubSoluciones\LivewireFluxTables\Columns\Column;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class SortManager
{
    public function apply(mixed $data, ?string $sort, string $direction, array $columns): mixed
    {
        if ($sort === null) {
            return $data;
        }

        $column = collect($columns)->first(
            fn (Column $candidate) => $candidate->field() === $sort && $candidate->isSortable()
        );

        if (! $column instanceof Column) {
            return $data;
        }

        if ($data instanceof EloquentBuilder || $data instanceof QueryBuilder) {
            $column->applySort($data, $direction);

            return $data;
        }

        if ($data instanceof Collection) {
            $sorted = $data->sortBy(fn ($row) => data_get($row, $column->field()), options: SORT_NATURAL);

            return $direction === 'desc' ? $sorted->reverse()->values() : $sorted->values();
        }

        return $data;
    }
}
