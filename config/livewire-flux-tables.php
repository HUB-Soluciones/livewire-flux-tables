<?php

return [
    'locale' => null, // null = inherit from app()->getLocale(). Set to 'en' or 'es' to force a specific locale.
    'default_per_page' => 15,
    'per_page_options' => [10, 15, 25, 50, 100],
    'persist_query_string' => true,
    'search_placeholder' => null, // null = use translation. Set a string to override.
    'default_sticky_width' => '12rem',
    'table_wrapper_class' => 'overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900',
    'table_scroll_class' => 'overflow-x-auto',

    // Zebra striping — filas alternadas.
    // Activar globalmente con true; cada componente puede sobrescribirlo con: protected ?bool $striped = true|false;
    'zebra_striping'   => false,
    'zebra_odd_class'  => 'bg-white dark:bg-zinc-900',
    'zebra_even_class' => 'bg-zinc-50 dark:bg-zinc-800/40',

    // Background base de filas (cuando zebra_striping = false).
    'row_base_class'   => 'bg-white dark:bg-zinc-900',

    // Background de celdas sticky. El header sticky usa sticky_header_class; el body sticky hereda el color de la fila.
    'sticky_header_class' => 'bg-zinc-50 dark:bg-zinc-800',
    'empty_state_heading' => null, // null = use translation. Set a string to override.
    'empty_state_message' => null, // null = use translation. Set a string to override.
    // Banner de selección masiva (visible cuando hay filas seleccionadas).
    'selection_banner_class'      => 'flex flex-wrap items-center justify-between gap-3 rounded-xl border border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-950 px-4 py-2.5 text-sm',
    'selection_banner_text_class' => 'text-sky-700 dark:text-sky-300',
    'selection_banner_link_class' => 'font-medium text-sky-700 dark:text-sky-300 underline underline-offset-2 transition hover:text-sky-900 dark:hover:text-sky-100',

    'pagination' => 'length_aware',
    'stubs_path' => 'stubs/livewire-flux-tables',
];
