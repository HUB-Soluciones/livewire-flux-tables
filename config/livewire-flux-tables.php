<?php

return [
    'locale' => null, // null = inherit from app()->getLocale(). Set to 'en' or 'es' to force a specific locale.
    'default_per_page' => 15,
    'per_page_options' => [10, 15, 25, 50, 100],
    'persist_query_string' => true,
    'search_placeholder' => null, // null = use translation. Set a string to override.
    'default_sticky_width' => '12rem',
    'table_wrapper_class' => 'overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm',
    'table_scroll_class' => 'overflow-x-auto',
    'empty_state_heading' => null, // null = use translation. Set a string to override.
    'empty_state_message' => null, // null = use translation. Set a string to override.
    'pagination' => 'length_aware',
    'stubs_path' => 'stubs/livewire-flux-tables',
];
