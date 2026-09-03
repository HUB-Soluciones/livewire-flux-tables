<?php

namespace HubSoluciones\LivewireFluxTables\Commands;

use HubSoluciones\LivewireFluxTables\Support\StubResolver;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeFluxTableCommand extends Command
{
    protected $signature = 'livewire-flux-tables:make
        {name : The Livewire table class name}
        {--model= : Model class or basename}
        {--path= : Nested path under app/Livewire}
        {--view : Generate a dedicated Blade view}
        {--view-path= : Nested path under resources/views}
        {--force : Overwrite existing files}
        {--with-filters : Include starter filters}
        {--with-filter-methods : Include starter filter methods}
        {--with-cell-views : Include a sample cell view}
        {--with-selection : Include a selection column with bulk actions}
        {--paginate=15 : Default per page value}
        {--stub=default : Stub set name}';

    protected $description = 'Generate a Livewire Flux table component.';

    public function __construct(
        protected Filesystem $files,
        protected StubResolver $stubResolver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $context = $this->buildContext();

        $this->writeFile(
            $context['component_path'],
            $this->compileStub('livewire-table.stub', $context)
        );

        if ($context['generate_view']) {
            $this->writeFile(
                $context['view_file_path'],
                $this->compileStub('livewire-table-view.stub', $context)
            );
        }

        if ($context['generate_cell_view']) {
            $this->writeFile(
                $context['cell_view_file_path'],
                $this->compileStub('cell-view.stub', $context)
            );
        }

        $this->info('Flux table generated successfully.');
        $this->line('Component: '.$context['component_path']);

        if ($context['generate_view']) {
            $this->line('View: '.$context['view_file_path']);
        }

        if ($context['generate_cell_view']) {
            $this->line('Cell view: '.$context['cell_view_file_path']);
        }

        return self::SUCCESS;
    }

    protected function buildContext(): array
    {
        $className = Str::studly(class_basename($this->argument('name')));
        $pathOption = trim((string) $this->option('path'), '\\/');
        $namespacePath = str_replace('/', '\\', $pathOption);
        $componentNamespace = trim('App\\Livewire\\'.$namespacePath, '\\');
        $componentPath = app_path('Livewire'.($pathOption ? DIRECTORY_SEPARATOR.$pathOption : '').DIRECTORY_SEPARATOR.$className.'.php');

        $viewPathOption = trim((string) $this->option('view-path'));
        $viewDirectory = $viewPathOption !== ''
            ? str_replace('\\', '/', trim($viewPathOption, '\\/'))
            : $this->defaultViewDirectory($pathOption);
        $viewName = trim($viewDirectory.'/'.$this->kebab($className), '/');
        $viewFilePath = resource_path('views'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $viewName).'.blade.php');
        $cellViewName = $viewDirectory.'/cells/status-badge';
        $cellViewFilePath = resource_path('views'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $cellViewName).'.blade.php');

        $modelOption = $this->option('model');
        $modelClass = $modelOption ? $this->qualifyModel($modelOption) : null;
        $modelBaseName = $modelClass ? class_basename($modelClass) : null;

        $withFilters = (bool) ($this->option('with-filters') || $this->option('with-filter-methods'));
        $withFilterMethods = (bool) $this->option('with-filter-methods');
        $withCellViews = (bool) $this->option('with-cell-views');
        $withSelection = (bool) $this->option('with-selection');
        $generateView = (bool) $this->option('view');

        $columns = $this->buildColumns($withCellViews, $cellViewName, $withSelection);
        $filters = $withFilters ? $this->buildFilters() : "        return [];";
        $filterMethods = $withFilterMethods ? $this->buildFilterMethods() : '';
        $selectionMethod = $withSelection ? $this->buildSelectionMethod() : '';

        $uses = [
            'HubSoluciones\\LivewireFluxTables\\Columns\\Column',
            'HubSoluciones\\LivewireFluxTables\\Livewire\\FluxTableComponent',
        ];

        if ($modelClass) {
            $uses[] = $modelClass;
        }

        if ($withFilters) {
            $uses[] = 'HubSoluciones\\LivewireFluxTables\\Filters\\DateRangeFilter';
            $uses[] = 'HubSoluciones\\LivewireFluxTables\\Filters\\SelectFilter';
            $uses[] = 'HubSoluciones\\LivewireFluxTables\\Filters\\TextFilter';
        }

        if ($withSelection) {
            $uses[] = 'HubSoluciones\\LivewireFluxTables\\Columns\\SelectionColumn';
        }

        $builder = $modelClass
            ? '        return '.$modelBaseName."::query();"
            : "        return collect();";

        return [
            'namespace' => $componentNamespace,
            'class' => $className,
            'uses' => $this->renderUses($uses),
            'model_comment' => $modelClass ? '' : "    // Replace collect() with an Eloquent or query builder source.\n",
            'builder' => $builder,
            'columns' => $columns,
            'filters' => $filters,
            'filter_methods' => $filterMethods,
            'selection_method' => $selectionMethod,
            'table_view_property' => $generateView ? "    protected ?string \$tableView = '".$viewName."';\n" : '',
            'paginate' => (string) ((int) $this->option('paginate') ?: 15),
            'view_name' => $viewName,
            'cell_view_name' => str_replace('/', '.', $cellViewName),
            'component_path' => $componentPath,
            'view_file_path' => $viewFilePath,
            'cell_view_file_path' => $cellViewFilePath,
            'generate_view' => $generateView,
            'generate_cell_view' => $withCellViews,
        ];
    }

    protected function compileStub(string $stub, array $context): string
    {
        $path = $this->stubResolver->resolve($stub);
        $contents = $this->files->get($path);

        foreach ($context as $key => $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $contents = str_replace('{{ '.$key.' }}', (string) $value, $contents);
        }

        return $contents;
    }

    protected function writeFile(string $path, string $contents): void
    {
        if ($this->files->exists($path) && ! $this->option('force')) {
            throw new \RuntimeException('File already exists: '.$path);
        }

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $contents);
    }

    protected function buildColumns(bool $withCellViews, string $cellViewName, bool $withSelection = false): string
    {
        $selectionColumn = $withSelection
            ? "\n            SelectionColumn::make()\n                ->resource('registro', 'registros')\n                ->bulkActions([\n                    'deleteSelected' => ['label' => 'Eliminar seleccionados', 'icon' => 'trash', 'variant' => 'danger'],\n                ]),"
            : '';

        $statusColumn = $withCellViews
            ? "\n            Column::make('Estatus', 'status')->view('".str_replace('/', '.', $cellViewName)."')->mobileHidden(),"
            : '';

        return <<<PHP
        return [{$selectionColumn}
            Column::make('ID', 'id')->sortable(),
            Column::make('Nombre', 'name')->searchable()->sortable()->sticky()->width('14rem')->mobileSummary(),
            Column::make('Email', 'email')->searchable()->sortable(),
            Column::make('Creado', 'created_at')->sortable()->mobileHidden(),{$statusColumn}
        ];
PHP;
    }

    protected function buildSelectionMethod(): string
    {
        return <<<'PHP'

    public function deleteSelected(): void
    {
        $ids = $this->selectAllRecords ? $this->allFilteredKeys() : $this->selectedKeys;
        // TODO: actuar sobre $ids
        $this->clearSelection();
    }
PHP;
    }

    protected function buildFilters(): string
    {
        return <<<PHP
        return [
            TextFilter::make('Nombre', 'name')->placeholder('Buscar por nombre'),
            SelectFilter::make('Estatus', 'status')->options([
                'active' => 'Activo',
                'inactive' => 'Inactivo',
            ]),
            DateRangeFilter::make('Periodo', 'created_at'),
        ];
PHP;
    }

    protected function buildFilterMethods(): string
    {
        return <<<PHP

    public function applyNameFilter(\$query, \$value): void
    {
        \$query->where('name', 'like', "%{\$value}%");
    }

    public function applyStatusFilter(\$query, \$value): void
    {
        \$query->where('status', \$value);
    }

    public function applyCreatedAtFilter(\$query, \$value): void
    {
        if (\$value['from'] ?? null) {
            \$query->whereDate('created_at', '>=', \$value['from']);
        }

        if (\$value['to'] ?? null) {
            \$query->whereDate('created_at', '<=', \$value['to']);
        }
    }
PHP;
    }

    protected function defaultViewDirectory(string $pathOption): string
    {
        $segments = array_filter(explode('/', str_replace('\\', '/', $pathOption)));
        $segments = array_map(fn (string $segment) => Str::kebab($segment), $segments);

        return trim('livewire/'.implode('/', $segments), '/');
    }

    protected function qualifyModel(string $model): string
    {
        return str_contains($model, '\\') ? ltrim($model, '\\') : 'App\\Models\\'.Str::studly($model);
    }

    protected function renderUses(array $uses): string
    {
        $uses = collect($uses)->unique()->sort()->values()->all();

        return implode("\n", array_map(fn (string $use) => 'use '.$use.';', $uses));
    }

    protected function kebab(string $value): string
    {
        return Str::kebab(class_basename($value));
    }
}
