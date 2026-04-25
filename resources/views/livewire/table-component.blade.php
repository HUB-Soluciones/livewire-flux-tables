<div class="space-y-4">
    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
        @include('livewire-flux-tables::components.toolbar', [
            'component' => $component,
            'filters' => $filters,
        ])
    </div>

    {{-- Banner de selección masiva --}}
    @if ($component->hasSelection() && count($component->selectedKeys) > 0)
        <div class="flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm">
            @if ($component->selectAllRecords)
                <span class="text-blue-700">
                    {{ __('All :count records are selected.', ['count' => method_exists($rows, 'total') ? $rows->total() : count($component->selectedKeys)]) }}
                </span>
                <button
                    type="button"
                    wire:click="clearSelection"
                    class="font-medium text-blue-700 underline underline-offset-2 transition hover:text-blue-900"
                >
                    {{ __('Clear selection') }}
                </button>
            @else
                <span class="text-blue-700">
                    {{ __(':count records selected on this page.', ['count' => count($component->selectedKeys)]) }}
                </span>
                @if (method_exists($rows, 'total') && $rows->total() > count($rows->items()))
                    <button
                        type="button"
                        wire:click="enableSelectAllRecords"
                        class="font-medium text-blue-700 underline underline-offset-2 transition hover:text-blue-900"
                    >
                        {{ __('Select all :count records', ['count' => $rows->total()]) }}
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="clearSelection"
                        class="font-medium text-blue-700 underline underline-offset-2 transition hover:text-blue-900"
                    >
                        {{ __('Clear selection') }}
                    </button>
                @endif
            @endif
        </div>
    @endif

    @include('livewire-flux-tables::components.table', [
        'component' => $component,
        'columns' => $columns,
        'rows' => $rows,
        'sticky' => $sticky,
    ])

    @include('livewire-flux-tables::components.pagination', [
        'rows' => $rows,
    ])
</div>
