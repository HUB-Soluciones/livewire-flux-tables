<?php

namespace HubSoluciones\LivewireFluxTables\Query;

use HubSoluciones\LivewireFluxTables\Filters\Filter;

class FilterManager
{
    public function apply(mixed $data, array $filters, array $state, object $component): mixed
    {
        foreach ($filters as $filter) {
            if (! $filter instanceof Filter) {
                continue;
            }

            $data = $filter->apply($data, $state[$filter->key()] ?? $filter->initialState(), $component);
        }

        return $data;
    }
}
