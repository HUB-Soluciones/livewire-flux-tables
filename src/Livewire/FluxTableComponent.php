<?php

namespace HubSoluciones\LivewireFluxTables\Livewire;

use HubSoluciones\LivewireFluxTables\Columns\Column;
use HubSoluciones\LivewireFluxTables\Filters\Filter;
use HubSoluciones\LivewireFluxTables\Query\QueryPipeline;
use HubSoluciones\LivewireFluxTables\Rendering\CellRenderer;
use HubSoluciones\LivewireFluxTables\Rendering\StickyColumnManager;
use HubSoluciones\LivewireFluxTables\State\TableConfiguration;
use HubSoluciones\LivewireFluxTables\State\TableState;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

abstract class FluxTableComponent extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $sort = null;

    public string $direction = 'asc';

    public int $perPage = 15;

    public array $tableFilters = [];

    protected ?string $tableView = null;

    protected QueryPipeline $queryPipeline;

    protected StickyColumnManager $stickyColumnManager;

    protected CellRenderer $cellRenderer;

    public function boot(
        QueryPipeline $queryPipeline,
        StickyColumnManager $stickyColumnManager,
        CellRenderer $cellRenderer,
    ): void {
        $this->queryPipeline = $queryPipeline;
        $this->stickyColumnManager = $stickyColumnManager;
        $this->cellRenderer = $cellRenderer;
    }

    public function mount(): void
    {
        $config = $this->configuration();

        $this->perPage = $this->perPage > 0 ? $this->perPage : $config->defaultPerPage;
        $this->direction = in_array($this->direction, ['asc', 'desc'], true) ? $this->direction : 'asc';

        foreach ($this->resolvedFilters() as $filter) {
            $this->tableFilters[$filter->key()] = Arr::get($this->tableFilters, $filter->key(), $filter->initialState());
        }

        if ($defaultSort = $this->defaultSort()) {
            $this->sort ??= $defaultSort;
            $this->direction = $this->sort === $defaultSort ? $this->defaultSortDirection() : $this->direction;
        }
    }

    protected function queryString(): array
    {
        if (! $this->persistsQueryString()) {
            return [];
        }

        $queryString = [
            'search' => ['except' => ''],
            'sort' => ['except' => null],
            'direction' => ['except' => 'asc'],
            'page' => ['except' => 1],
            'perPage' => ['except' => $this->configuration()->defaultPerPage],
        ];

        foreach ($this->resolvedFilters() as $filter) {
            $queryString['tableFilters.'.$filter->key()] = [
                'as' => $filter->key(),
                'except' => $filter->initialState(),
            ];
        }

        return $queryString;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedTableFilters(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $column = collect($this->resolvedColumns())->first(
            fn (Column $candidate) => $candidate->field() === $field && $candidate->isSortable()
        );

        if (! $column instanceof Column) {
            return;
        }

        if ($this->sort === $field) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }

        $this->resetPage();
    }

    public function resolvedColumns(): array
    {
        return array_values(array_filter(
            $this->columns(),
            fn ($column) => $column instanceof Column
        ));
    }

    public function resolvedFilters(): array
    {
        return array_values(array_filter(
            $this->filters(),
            fn ($filter) => $filter instanceof Filter
        ));
    }

    public function tableState(): TableState
    {
        return new TableState(
            search: $this->search,
            sort: $this->sanitizeSort(),
            direction: $this->direction === 'desc' ? 'desc' : 'asc',
            perPage: $this->sanitizePerPage(),
            page: (int) request('page', 1),
            filters: $this->sanitizeFilters()
        );
    }

    public function rows(): Paginator
    {
        return $this->queryPipeline->process($this->dataSource(), $this);
    }

    public function stickyMetadata(): array
    {
        return $this->stickyColumnManager->map(
            $this->resolvedColumns(),
            config('livewire-flux-tables.default_sticky_width', '12rem')
        );
    }

    public function renderCell(Column $column, mixed $row): array
    {
        return $this->cellRenderer->render($column, $row, $this);
    }

    public function searchPlaceholder(): string
    {
        return $this->configuration()->searchPlaceholder;
    }

    public function perPageOptions(): array
    {
        return $this->configuration()->perPageOptions;
    }

    public function emptyStateHeading(): string
    {
        return $this->configuration()->emptyStateHeading;
    }

    public function emptyStateMessage(): string
    {
        return $this->configuration()->emptyStateMessage;
    }

    public function paginatesSimply(): bool
    {
        return $this->configuration()->pagination === 'simple';
    }

    public function persistsQueryString(): bool
    {
        return $this->configuration()->persistQueryString;
    }

    public function configuration(): TableConfiguration
    {
        return new TableConfiguration(
            defaultPerPage: $this->defaultPerPage(),
            perPageOptions: $this->declaredPerPageOptions(),
            persistQueryString: $this->usesQueryStringPersistence(),
            searchPlaceholder: $this->searchPlaceholderText(),
            pagination: $this->paginationMethod(),
            emptyStateHeading: $this->emptyHeading(),
            emptyStateMessage: $this->emptyMessage(),
        );
    }

    protected function dataSource(): EloquentBuilder|QueryBuilder|Collection|array|Paginator
    {
        if (method_exists($this, 'builder')) {
            return $this->builder();
        }

        if (method_exists($this, 'query')) {
            return $this->query();
        }

        if (method_exists($this, 'records')) {
            return $this->records();
        }

        throw new \RuntimeException('FluxTableComponent requires builder(), query(), or records().');
    }

    protected function sanitizeFilters(): array
    {
        $state = [];

        foreach ($this->resolvedFilters() as $filter) {
            $state[$filter->key()] = Arr::get($this->tableFilters, $filter->key(), $filter->initialState());
        }

        return $state;
    }

    protected function sanitizeSort(): ?string
    {
        $allowed = collect($this->resolvedColumns())
            ->filter(fn (Column $column) => $column->isSortable())
            ->map(fn (Column $column) => $column->field())
            ->all();

        return in_array($this->sort, $allowed, true) ? $this->sort : null;
    }

    protected function sanitizePerPage(): int
    {
        $perPage = (int) $this->perPage;

        return in_array($perPage, $this->declaredPerPageOptions(), true)
            ? $perPage
            : $this->defaultPerPage();
    }

    protected function defaultPerPage(): int
    {
        return (int) config('livewire-flux-tables.default_per_page', 15);
    }

    protected function declaredPerPageOptions(): array
    {
        return array_values(config('livewire-flux-tables.per_page_options', [10, 15, 25, 50]));
    }

    protected function usesQueryStringPersistence(): bool
    {
        return (bool) config('livewire-flux-tables.persist_query_string', true);
    }

    protected function searchPlaceholderText(): string
    {
        return (string) config('livewire-flux-tables.search_placeholder', 'Buscar registros...');
    }

    protected function paginationMethod(): string
    {
        return (string) config('livewire-flux-tables.pagination', 'length_aware');
    }

    protected function emptyHeading(): string
    {
        return (string) config('livewire-flux-tables.empty_state_heading', 'Sin resultados');
    }

    protected function emptyMessage(): string
    {
        return (string) config('livewire-flux-tables.empty_state_message', 'No hay registros que coincidan con los criterios actuales.');
    }

    protected function defaultSort(): ?string
    {
        return null;
    }

    protected function defaultSortDirection(): string
    {
        return 'asc';
    }

    public function filters(): array
    {
        return [];
    }

    abstract public function columns(): array;

    public function render()
    {
        return view($this->tableView ?: 'livewire-flux-tables::livewire.table-component', [
            'component' => $this,
            'columns' => $this->resolvedColumns(),
            'filters' => $this->resolvedFilters(),
            'rows' => $this->rows(),
            'sticky' => $this->stickyMetadata(),
        ]);
    }
}
