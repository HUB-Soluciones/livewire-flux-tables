<?php

namespace Workbench\App\Livewire;

use HubSoluciones\LivewireFluxTables\Columns\Column;
use HubSoluciones\LivewireFluxTables\Columns\SelectionColumn;
use HubSoluciones\LivewireFluxTables\Filters\DateRangeFilter;
use HubSoluciones\LivewireFluxTables\Filters\SelectFilter;
use HubSoluciones\LivewireFluxTables\Filters\TextFilter;
use HubSoluciones\LivewireFluxTables\Livewire\FluxTableComponent;
use Workbench\App\Models\User;

class UsersTable extends FluxTableComponent
{
    protected ?bool $striped = true;

    public function builder()
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            SelectionColumn::make()
                ->resource('usuario', 'usuarios')
                ->bulkActions([
                    'exportCsv' => [
                        'label' => 'Exportar CSV',
                        'icon' => 'arrow-down-tray',
                        'variant' => 'primary',
                    ],
                    'exportJson' => [
                        'label' => 'Exportar JSON',
                        'icon' => 'arrow-down-tray',
                    ],
                    'markActive' => 'Marcar activos',
                    'markInactive' => [
                        'label' => 'Marcar inactivos',
                        'variant' => 'danger',
                    ],
                ]),
            Column::make('ID', 'id')->sortable()->sticky('left')->width('4rem')->align('right')->mobileSummary(),
            Column::make('Nombre', 'name')->searchable()->sortable()->sticky('left')->width('14rem')->mobileSummary(),
            Column::make('Email', 'email')->searchable()->sortable(),
            Column::make('Rol', 'role')->sortable()->format(fn ($v) => match ($v) {
                'admin'  => '👑 Admin',
                'editor' => '✏️ Editor',
                default  => '👁️ Visor',
            }),
            Column::make('Estado', 'status')->sortable()->format(fn ($v) => $v === 'active' ? '🟢 Activo' : '🔴 Inactivo')->mobileSummary(),
            Column::make('Registrado', 'created_at')->sortable()->sticky('right')->width('8rem')->format(fn ($v) => \Carbon\Carbon::parse($v)->format('d/m/Y')),
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

    public function exportCsv()
    {
        $users = $this->selectedUsers();

        return response()->streamDownload(function () use ($users): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', 'Nombre', 'Email', 'Rol', 'Estado', 'Registrado']);

            foreach ($users as $user) {
                fputcsv($output, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->status,
                    $user->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($output);
        }, 'usuarios-seleccionados.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportJson()
    {
        $payload = $this->selectedUsers()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at?->toIso8601String(),
            ])
            ->values()
            ->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response()->streamDownload(
            fn () => print($payload),
            'usuarios-seleccionados.json',
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }

    public function markActive(): void
    {
        $this->updateSelectedStatus('active');
    }

    public function markInactive(): void
    {
        $this->updateSelectedStatus('inactive');
    }

    private function selectedUserIds(): array
    {
        return $this->selectAllRecords ? $this->allFilteredKeys() : $this->selectedKeys;
    }

    private function selectedUsers()
    {
        return User::query()
            ->whereKey($this->selectedUserIds())
            ->orderBy('name')
            ->get();
    }

    private function updateSelectedStatus(string $status): void
    {
        User::query()->whereKey($this->selectedUserIds())->update(['status' => $status]);
        $this->clearSelection();
    }
}
