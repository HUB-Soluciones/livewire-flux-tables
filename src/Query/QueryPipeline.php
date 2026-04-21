<?php

namespace HubSoluciones\LivewireFluxTables\Query;

use HubSoluciones\LivewireFluxTables\Livewire\FluxTableComponent;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class QueryPipeline
{
    public function __construct(
        protected SearchEngine $searchEngine = new SearchEngine(),
        protected FilterManager $filterManager = new FilterManager(),
        protected SortManager $sortManager = new SortManager(),
    ) {
    }

    public function process(mixed $source, FluxTableComponent $component): mixed
    {
        if ($source instanceof PaginatorContract) {
            return $source;
        }

        $columns = $component->resolvedColumns();
        $filters = $component->resolvedFilters();
        $state = $component->tableState();

        if ($source instanceof EloquentBuilder || $source instanceof QueryBuilder) {
            $query = clone $source;

            $query = $this->searchEngine->apply($query, $state->search, $columns);
            $query = $this->filterManager->apply($query, $filters, $state->filters, $component);
            $query = $this->sortManager->apply($query, $state->sort, $state->direction, $columns);

            return $component->paginatesSimply()
                ? $query->simplePaginate($state->perPage)
                : $query->paginate($state->perPage);
        }

        $collection = $source instanceof Collection ? $source->values() : collect($source)->values();

        $collection = $this->searchEngine->apply($collection, $state->search, $columns);
        $collection = $this->filterManager->apply($collection, $filters, $state->filters, $component);
        $collection = $this->sortManager->apply($collection, $state->sort, $state->direction, $columns);

        $page = Paginator::resolveCurrentPage() ?: 1;
        $items = $collection->forPage($page, $state->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $state->perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }
}
