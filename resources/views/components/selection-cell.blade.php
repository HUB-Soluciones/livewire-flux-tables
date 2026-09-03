@php
    $checked = $table->isRowSelected($row);
    $disabled = ! $column->isRowSelectable($row, $table);
    $selectionScope = ($mobile ?? false) ? 'mobile' : 'desktop';
    $selectionKey = $table->resolveRowKey($row);
@endphp

{{-- El wire:key va en este wrapper (no en <flux:checkbox> directamente): Livewire
     inyecta PHP crudo antes de cualquier atributo wire:key, lo que rompe el
     compilador de tags de Flux si se coloca sobre la propia etiqueta flux:*. --}}
<span wire:key="selection-{{ $selectionScope }}-{{ $selectionKey }}-{{ $checked ? 'selected' : 'unselected' }}" class="inline-flex">
    <flux:checkbox
        aria-label="{{ __('Select record :key', ['key' => $selectionKey]) }}"
        wire:click="toggleRow('{{ $selectionKey }}')"
        :checked="$checked"
        :disabled="$disabled"
        class="data-loading:pointer-events-none data-loading:opacity-50"
    />
</span>
