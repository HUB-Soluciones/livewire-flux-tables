<?php

namespace HubSoluciones\LivewireFluxTables\Tests;

use HubSoluciones\LivewireFluxTables\LivewireFluxTablesServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $viewCachePath = sys_get_temp_dir().'/livewire-flux-tables-views';
        if (! is_dir($viewCachePath)) {
            mkdir($viewCachePath, 0755, true);
        }
        $app['config']->set('view.compiled', $viewCachePath);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('livewire-flux-tables.default_per_page', 2);
        $app['config']->set('livewire-flux-tables.per_page_options', [2, 10, 15, 25, 50, 100]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            LivewireFluxTablesServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['view']->addNamespace('livewire-flux-tables-test', __DIR__.'/Fixtures/Views');

        Schema::dropIfExists('fixture_users');
        Schema::create('fixture_users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('role');
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }
}
