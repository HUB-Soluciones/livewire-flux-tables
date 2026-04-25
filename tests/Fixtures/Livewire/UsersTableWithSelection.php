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

    public bool $exported = false;

    public function builder()
    {
        return FixtureUser::query();
    }

    public function columns(): array
    {
        return [
            SelectionColumn::make()
                ->resource('socio', 'socios')
                ->bulkActions([
                    'markInactive' => 'Marcar inactivos',
                    'export' => [
                        'label'   => 'Exportar seleccionados',
                        'icon'    => 'arrow-down-tray',
                        'variant' => 'primary',
                    ],
                ]),
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

    public function export(): void
    {
        $this->exported = true;
    }
}
