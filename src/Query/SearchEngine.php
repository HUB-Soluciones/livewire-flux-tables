<?php

namespace HubSoluciones\LivewireFluxTables\Query;

use HubSoluciones\LivewireFluxTables\Columns\Column;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class SearchEngine
{
    public function apply(mixed $data, string $search, array $columns): mixed
    {
        if ($search === '') {
            return $data;
        }

        $searchableColumns = array_values(array_filter(
            $columns,
            fn (Column $column) => $column->isSearchable()
        ));

        if ($searchableColumns === []) {
            return $data;
        }

        if ($data instanceof EloquentBuilder || $data instanceof QueryBuilder) {
            $data->where(function ($query) use ($searchableColumns, $search): void {
                foreach ($searchableColumns as $column) {
                    $column->applySearch($query, $search);
                }
            });

            return $data;
        }

        if ($data instanceof Collection) {
            $needle = mb_strtolower($search);

            return $data->filter(function ($row) use ($searchableColumns, $needle) {
                foreach ($searchableColumns as $column) {
                    $value = mb_strtolower((string) $column->resolveValue($row));

                    if (str_contains($value, $needle)) {
                        return true;
                    }
                }

                return false;
            })->values();
        }

        return $data;
    }
}
