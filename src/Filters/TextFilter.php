<?php

namespace HubSoluciones\LivewireFluxTables\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class TextFilter extends Filter
{
    public function type(): string
    {
        return 'text';
    }

    protected function applyDefault(mixed $data, mixed $value): mixed
    {
        if ($data instanceof EloquentBuilder || $data instanceof QueryBuilder) {
            $data->where($this->key(), 'like', '%'.$value.'%');

            return $data;
        }

        if ($data instanceof Collection) {
            return $this->applyToCollection(
                $data,
                $value,
                fn ($row, $term) => str_contains(mb_strtolower((string) data_get($row, $this->key(), '')), mb_strtolower((string) $term))
            );
        }

        return $data;
    }
}
