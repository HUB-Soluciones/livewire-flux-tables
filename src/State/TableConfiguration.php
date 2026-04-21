<?php

namespace HubSoluciones\LivewireFluxTables\State;

class TableConfiguration
{
    public function __construct(
        public int $defaultPerPage,
        public array $perPageOptions,
        public bool $persistQueryString,
        public string $searchPlaceholder,
        public string $pagination,
        public string $emptyStateHeading,
        public string $emptyStateMessage,
    ) {
    }
}
