<?php

return [
    'locale' => null, // null = inherit from app()->getLocale(). Set to 'en' or 'es' to force a specific locale.
    'default_per_page' => 15,
    'per_page_options' => [10, 15, 25, 50, 100],
    'persist_query_string' => true,
    'search_placeholder' => null, // null = use translation. Set a string to override.
    'default_sticky_width' => '12rem',
    // Controls only the mobile sort control; flux:table is available in free Flux UI.
    'flux_tier' => 'auto', // auto | base | pro
    'mobile_layout' => 'cards', // cards | table
    'table_wrapper_class' => 'overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900',
    // Inset horizontal en los bordes de la tabla. Flux aplica `first:ps-0 last:pe-0` a
    // th/td asumiendo una tabla a sangre; como aquí va dentro de una card con borde, se
    // restituye el padding. Gana por especificidad (0,2,1 vs 0,2,0), sin !important.
    'table_edge_padding_class' => '[&_th:first-child]:ps-4 [&_th:last-child]:pe-4 [&_td:first-child]:ps-4 [&_td:last-child]:pe-4',
    // @deprecated No la consume ninguna vista del paquete (el scroll horizontal lo maneja
    // flux:table vía <ui-table-scroll-area>). Se mantiene por compatibilidad hacia atrás.
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
    // Neutro por diseño — coincide con las tarjetas del toolbar y la tabla en vez
    // de destacar con color; el color se reserva para la acción "danger".
    'selection_banner_class'      => 'flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-3 text-sm shadow-sm',
    'selection_banner_text_class' => 'font-medium text-zinc-700 dark:text-zinc-200',
    'selection_banner_link_class' => 'font-medium text-zinc-500 dark:text-zinc-400 underline-offset-2 transition hover:text-zinc-900 dark:hover:text-white hover:underline',

    'pagination' => 'length_aware',
    'stubs_path' => 'stubs/livewire-flux-tables',
];
