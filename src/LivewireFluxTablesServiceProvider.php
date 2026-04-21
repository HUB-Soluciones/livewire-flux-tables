<?php

namespace HubSoluciones\LivewireFluxTables;

use HubSoluciones\LivewireFluxTables\Commands\MakeFluxTableCommand;
use HubSoluciones\LivewireFluxTables\Commands\ScaffoldFluxTableCommand;
use Illuminate\Support\ServiceProvider;

class LivewireFluxTablesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/livewire-flux-tables.php',
            'livewire-flux-tables'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'livewire-flux-tables');

        $this->publishes([
            __DIR__.'/../config/livewire-flux-tables.php' => config_path('livewire-flux-tables.php'),
        ], 'livewire-flux-tables-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/livewire-flux-tables'),
        ], 'livewire-flux-tables-views');

        $this->publishes([
            __DIR__.'/../resources/stubs' => base_path(config('livewire-flux-tables.stubs_path', 'stubs/livewire-flux-tables')),
        ], 'livewire-flux-tables-stubs');

        $this->publishes([
            __DIR__.'/../.claude/skills/laravel-flux-table-package' => base_path('.claude/skills/laravel-flux-table-package'),
        ], 'livewire-flux-tables-skill');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeFluxTableCommand::class,
                ScaffoldFluxTableCommand::class,
            ]);
        }
    }
}
