<?php

return [
    'start-providers' => [
        'Workbench\App\Providers\WorkbenchServiceProvider',
    ],
    'assets' => [],
    'sync' => [],
    'build' => [],
    'install' => [
        'extra-commands' => [
            'migrate:fresh --seed',
        ],
    ],
];
