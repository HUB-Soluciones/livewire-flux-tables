<?php

namespace HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire;

use HubSoluciones\LivewireFluxTables\Columns\Column;
use HubSoluciones\LivewireFluxTables\Columns\SelectionColumn;
use HubSoluciones\LivewireFluxTables\Livewire\FluxTableComponent;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Models\FixtureUser;

class UsersTableWithSelection extends FluxTableComponent
{
    public int $perPage = 2;

    public array $markedInactive = [];

    public function builder()
    {
        return FixtureUser::query();
    }

    public function columns(): array
    {
        return [
            SelectionColumn::make()->bulkActions(['markInactive' => 'Marcar inactivos']),
            Column::make('ID', 'id')->sortable(),
            Column::make('Nombre', 'name')->searchable()->sortable(),
            Column::make('Email', 'email')->searchable(),
        ];
    }

    public function markInactive(): void
    {
        $this->markedInactive = $this->selectAllRecords
            ? $this->allFilteredKeys()
            : $this->selectedKeys;

        $this->clearSelection();
    }
}
