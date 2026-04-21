<?php

namespace HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire;

use HubSoluciones\LivewireFluxTables\Columns\Column;
use HubSoluciones\LivewireFluxTables\Filters\DateFilter;
use HubSoluciones\LivewireFluxTables\Filters\DateRangeFilter;
use HubSoluciones\LivewireFluxTables\Filters\SelectFilter;
use HubSoluciones\LivewireFluxTables\Filters\TextFilter;
use HubSoluciones\LivewireFluxTables\Livewire\FluxTableComponent;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Models\FixtureUser;

class UsersTable extends FluxTableComponent
{
    public int $perPage = 2;

    public function builder()
    {
        return FixtureUser::query();
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')->sortable(),
            Column::make('Nombre', 'name')->searchable()->sortable()->sticky()->width('12rem')->stackOnMobile(),
            Column::make('Email', 'email')->searchable()->sortable(),
            Column::make('Rol', 'role')->sortable(),
            Column::make('Estatus', 'status')->view('livewire-flux-tables-test::status-cell')->mobileHidden(),
            Column::make('Creado', 'created_at')->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('Nombre', 'name'),
            SelectFilter::make('Rol', 'role')->options([
                'admin' => 'Admin',
                'user' => 'Usuario',
            ]),
            DateFilter::make('Fecha', 'created_at'),
            DateRangeFilter::make('Periodo', 'created_between'),
        ];
    }

    public function applyCreatedBetweenFilter($query, $value): void
    {
        if ($value['from'] ?? null) {
            $query->whereDate('created_at', '>=', $value['from']);
        }

        if ($value['to'] ?? null) {
            $query->whereDate('created_at', '<=', $value['to']);
        }
    }
}
