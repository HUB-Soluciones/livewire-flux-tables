<?php

namespace HubSoluciones\LivewireFluxTables\State;

class TableState
{
    public function __construct(
        public string $search = '',
        public ?string $sort = null,
        public string $direction = 'asc',
        public int $perPage = 15,
        public int $page = 1,
        public array $filters = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'sort' => $this->sort,
            'direction' => $this->direction,
            'perPage' => $this->perPage,
            'page' => $this->page,
            'filters' => $this->filters,
        ];
    }
}
