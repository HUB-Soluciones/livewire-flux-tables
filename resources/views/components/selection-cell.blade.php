@php
    $checked = $component->isRowSelected($row);
    $disabled = ! $column->isRowSelectable($row, $component);
@endphp

<input
    type="checkbox"
    wire:click="toggleRow('{{ $component->resolveRowKey($row) }}')"
    {{ $checked ? 'checked' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    class="h-4 w-4 cursor-pointer rounded border-zinc-300 dark:border-zinc-600 text-blue-600 transition focus:ring-2 focus:ring-blue-500 focus:ring-offset-0 dark:focus:ring-offset-zinc-900 disabled:cursor-not-allowed disabled:opacity-50"
/>
