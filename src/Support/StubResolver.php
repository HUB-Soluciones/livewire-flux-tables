<?php

namespace HubSoluciones\LivewireFluxTables\Support;

class StubResolver
{
    public function resolve(string $stub): string
    {
        $published = base_path(config('livewire-flux-tables.stubs_path').DIRECTORY_SEPARATOR.$stub);

        if (file_exists($published)) {
            return $published;
        }

        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.$stub;
    }
}
