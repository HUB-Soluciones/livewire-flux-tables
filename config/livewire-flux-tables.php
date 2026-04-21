<?php

return [
    'default_per_page' => 15,
    'per_page_options' => [10, 15, 25, 50, 100],
    'persist_query_string' => true,
    'search_placeholder' => 'Buscar registros...',
    'default_sticky_width' => '12rem',
    'table_wrapper_class' => 'overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm',
    'table_scroll_class' => 'overflow-x-auto',
    'empty_state_heading' => 'Sin resultados',
    'empty_state_message' => 'No hay registros que coincidan con los criterios actuales.',
    'pagination' => 'length_aware',
    'stubs_path' => 'stubs/livewire-flux-tables',
];
