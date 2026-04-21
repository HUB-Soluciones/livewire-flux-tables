<?php

namespace HubSoluciones\LivewireFluxTables\Commands;

use Illuminate\Console\Command;

class ScaffoldFluxTableCommand extends Command
{
    protected $signature = 'livewire-flux-tables:scaffold
        {name : The Livewire table class name}
        {--model= : Model class or basename}
        {--path= : Nested path under app/Livewire}
        {--view-path= : Nested path under resources/views}
        {--force : Overwrite existing files}
        {--paginate=15 : Default per page value}
        {--stub=default : Stub set name}';

    protected $description = 'Generate a scaffolded Livewire Flux table with view, filters, and sample cell views.';

    public function handle(): int
    {
        return (int) $this->call('livewire-flux-tables:make', [
            'name' => $this->argument('name'),
            '--model' => $this->option('model'),
            '--path' => $this->option('path'),
            '--view' => true,
            '--view-path' => $this->option('view-path'),
            '--force' => $this->option('force'),
            '--with-filters' => true,
            '--with-filter-methods' => true,
            '--with-cell-views' => true,
            '--paginate' => $this->option('paginate'),
            '--stub' => $this->option('stub'),
        ]);
    }
}
