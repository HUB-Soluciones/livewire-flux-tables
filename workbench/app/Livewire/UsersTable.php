<?php

namespace Workbench\App\Livewire;

use HubSoluciones\LivewireFluxTables\Columns\Column;
use HubSoluciones\LivewireFluxTables\Filters\DateRangeFilter;
use HubSoluciones\LivewireFluxTables\Filters\SelectFilter;
use HubSoluciones\LivewireFluxTables\Filters\TextFilter;
use HubSoluciones\LivewireFluxTables\Livewire\FluxTableComponent;
use Workbench\App\Models\User;

class UsersTable extends FluxTableComponent
{
    public function builder()
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')->sortable()->width('4rem')->align('right'),
            Column::make('Nombre', 'name')->searchable()->sortable()->sticky()->width('14rem')->stackOnMobile(),
            Column::make('Email', 'email')->searchable()->sortable(),
            Column::make('Rol', 'role')->sortable()->format(fn ($v) => match ($v) {
                'admin'  => '👑 Admin',
                'editor' => '✏️ Editor',
                default  => '👁️ Visor',
            }),
            Column::make('Estado', 'status')->sortable()->mobileHidden()->format(fn ($v) => $v === 'active' ? '🟢 Activo' : '🔴 Inactivo'),
            Column::make('Registrado', 'created_at')->sortable()->mobileHidden()->format(fn ($v) => \Carbon\Carbon::parse($v)->format('d/m/Y')),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('Nombre', 'name'),
            SelectFilter::make('Rol', 'role')->options([
                'admin'  => 'Administrador',
                'editor' => 'Editor',
                'viewer' => 'Visor',
            ]),
            SelectFilter::make('Estado', 'status')->options([
                'active'   => 'Activo',
                'inactive' => 'Inactivo',
            ]),
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

    public function defaultSort(): string
    {
        return 'name';
    }
}
