<?php

namespace HubSoluciones\LivewireFluxTables\Tests\Feature;

use HubSoluciones\LivewireFluxTables\Tests\TestCase;
use Illuminate\Support\Facades\File;

class MakeFluxTableCommandTest extends TestCase
{
    public function test_make_command_generates_component_and_view_files(): void
    {
        $this->artisan('livewire-flux-tables:make', [
            'name' => 'UsersTable',
            '--model' => 'User',
            '--path' => 'Tables/Users',
            '--view' => true,
            '--view-path' => 'livewire/tables/users',
            '--with-filters' => true,
            '--with-filter-methods' => true,
            '--with-cell-views' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $componentPath = app_path('Livewire/Tables/Users/UsersTable.php');
        $viewPath = resource_path('views/livewire/tables/users/users-table.blade.php');
        $cellViewPath = resource_path('views/livewire/tables/users/cells/status-badge.blade.php');

        $this->assertFileExists($componentPath);
        $this->assertFileExists($viewPath);
        $this->assertFileExists($cellViewPath);
    }

    public function test_make_command_with_selection_flag_includes_selection_column(): void
    {
        $this->artisan('livewire-flux-tables:make', [
            'name' => 'OrdersTable',
            '--path' => 'Orders',
            '--with-selection' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $componentPath = app_path('Livewire/Orders/OrdersTable.php');
        $contents = \Illuminate\Support\Facades\File::get($componentPath);

        $this->assertStringContainsString('use HubSoluciones\LivewireFluxTables\Columns\SelectionColumn;', $contents);
        $this->assertStringContainsString('SelectionColumn::make()', $contents);
        $this->assertStringContainsString('deleteSelected', $contents);
        $this->assertStringContainsString('allFilteredKeys', $contents);
    }

    public function test_make_command_generates_filter_methods(): void
    {
        $this->artisan('livewire-flux-tables:make', [
            'name' => 'ReportTable',
            '--path' => 'Reports/Sales',
            '--with-filters' => true,
            '--with-filter-methods' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $componentPath = app_path('Livewire/Reports/Sales/ReportTable.php');
        $contents = File::get($componentPath);

        $this->assertStringContainsString('applyNameFilter', $contents);
        $this->assertStringContainsString('applyStatusFilter', $contents);
        $this->assertStringContainsString('applyCreatedAtFilter', $contents);
    }
}
