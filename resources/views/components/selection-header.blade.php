@php
    $selectionState = $table->pageSelectionState();
    $pageCount = count($table->pageKeys());
    $totalCount = $table->totalRecords();
@endphp

<div class="inline-flex items-center gap-0.5">
    {{-- El wire:key va en este wrapper, no en <flux:checkbox> directamente: ver
         nota en selection-cell.blade.php. --}}
    <span wire:key="selection-header-{{ $selectionState }}" class="inline-flex">
        <flux:checkbox
            aria-label="{{ __('Select records') }}"
            wire:click="togglePageSelection"
            :checked="$selectionState === 'full'"
            x-effect="$el.indeterminate = {{ $selectionState === 'partial' ? 'true' : 'false' }}"
            class="data-loading:pointer-events-none data-loading:opacity-50"
        />
    </span>

    <flux:dropdown position="bottom" align="start">
        <flux:button
            type="button"
            variant="subtle"
            size="xs"
            icon="chevron-down"
            aria-label="{{ __('Selection options') }}"
            class="-me-1 data-loading:pointer-events-none data-loading:opacity-50"
        />

        <flux:menu>
            <flux:menu.item wire:click="togglePageSelection">
                {{ __('Select page (:count)', ['count' => $pageCount]) }}
            </flux:menu.item>

            @if ($totalCount !== null && $totalCount > $pageCount)
                <flux:menu.item wire:click="enableSelectAllRecords">
                    {{ __('Select all :count records', ['count' => $totalCount]) }}
                </flux:menu.item>
            @endif

            <flux:menu.separator />

            <flux:menu.item variant="danger" wire:click="clearSelection">
                {{ __('Clear selection') }}
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</div>
