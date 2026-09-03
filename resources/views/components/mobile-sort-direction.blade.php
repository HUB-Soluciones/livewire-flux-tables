@if ($table->sort)
    @if ($table->direction === 'asc')
        <flux:button
            type="button"
            variant="outline"
            size="sm"
            icon="arrow-down"
            wire:click="toggleSortDirection"
            aria-label="{{ __('Descending') }}"
            class="data-loading:pointer-events-none data-loading:opacity-50"
        />
    @else
        <flux:button
            type="button"
            variant="outline"
            size="sm"
            icon="arrow-up"
            wire:click="toggleSortDirection"
            aria-label="{{ __('Ascending') }}"
            class="data-loading:pointer-events-none data-loading:opacity-50"
        />
    @endif
@endif
