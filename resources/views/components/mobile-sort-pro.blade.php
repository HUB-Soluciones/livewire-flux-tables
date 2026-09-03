<div class="flex w-full items-center gap-2 md:hidden" role="group" aria-label="{{ __('Sort records') }}">
    <flux:select
        variant="listbox"
        size="sm"
        value="{{ $table->sort ?? '' }}"
        wire:change="setSortField($event.target.value)"
        placeholder="{{ __('Sort by') }}"
        aria-label="{{ __('Sort by') }}"
        class="min-w-0 flex-1 data-loading:pointer-events-none data-loading:opacity-50"
    >
        @foreach ($mobileSortColumns as $column)
            <flux:select.option value="{{ $column->field() }}">{{ $column->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    @include('livewire-flux-tables::components.mobile-sort-direction', ['table' => $table])
</div>
