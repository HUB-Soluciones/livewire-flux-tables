<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Workbench\App\Livewire\UsersTable;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->setLocale('es');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'workbench');

        Livewire::component('users-table', UsersTable::class);

        // El demo usa Tailwind vía CDN (sin build de Vite), así que no hay un
        // `@import 'flux'` que traiga flux.css. Lo servimos aparte para que los
        // componentes ui-* (dropdown, menu, checkbox…) tengan sus estilos.
        Route::get('/flux-demo.css', fn () => response()->file(
            base_path('vendor/livewire/flux/dist/flux.css'),
            ['Content-Type' => 'text/css']
        ));
    }
}
