<div class="space-y-4">
    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm">
        @include('livewire-flux-tables::components.toolbar', [
            'component' => $component,
            'filters' => $filters,
        ])
    </div>

    {{-- Banner de selección masiva --}}
    @if ($component->hasSelection() && count($component->selectedKeys) > 0)
        @php
            $selCol          = $component->selectionColumn();
            $bulkActionsRaw  = $selCol->getNormalizedBulkActions();
            $totalRecords    = method_exists($rows, 'total') ? $rows->total() : count($component->selectedKeys);
            $selectedCount   = $component->selectAllRecords ? $totalRecords : count($component->selectedKeys);
            $resourceWord    = $selCol->getResourceLabel($selectedCount);
            $bannerClass     = config('livewire-flux-tables.selection_banner_class');
            $bannerTextClass = config('livewire-flux-tables.selection_banner_text_class');
            $bannerLinkClass = config('livewire-flux-tables.selection_banner_link_class');
            $hasMorePages    = method_exists($rows, 'total') && $rows->total() > count($rows->items());
        @endphp

        <div class="{{ $bannerClass }}">
            <div class="flex flex-wrap items-center gap-3 min-w-0">
                @if ($component->selectAllRecords)
                    <span class="{{ $bannerTextClass }}">
                        {{ __('All :count :resource are selected.', ['count' => $totalRecords, 'resource' => $resourceWord]) }}
                    </span>
                    <button type="button" wire:click="clearSelection" class="{{ $bannerLinkClass }}">
                        {{ __('Clear selection') }}
                    </button>
                @else
                    <span class="{{ $bannerTextClass }}">
                        {{ __(':count :resource selected on this page.', ['count' => count($component->selectedKeys), 'resource' => $resourceWord]) }}
                    </span>
                    @if ($hasMorePages)
                        <button type="button" wire:click="enableSelectAllRecords" class="{{ $bannerLinkClass }}">
                            {{ __('Select all :count :resource', ['count' => $rows->total(), 'resource' => $selCol->getResourcePlural()]) }}
                        </button>
                    @else
                        <button type="button" wire:click="clearSelection" class="{{ $bannerLinkClass }}">
                            {{ __('Clear selection') }}
                        </button>
                    @endif
                @endif
            </div>

            @if (!empty($bulkActionsRaw))
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($bulkActionsRaw as $method => $action)
                        @php
                            $btnClass = match ($action['variant'] ?? null) {
                                'primary' => 'inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 dark:focus:ring-offset-sky-950',
                                'danger'  => 'inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1',
                                default   => 'inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200 shadow-sm transition hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-400',
                            };
                        @endphp
                        <button type="button" wire:click="{{ $method }}" class="{{ $btnClass }}">
                            @if (!empty($action['icon']))
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/>
                                </svg>
                            @endif
                            {{ __($action['label']) }}
                        </button>
                    @endforeach
                </div>
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
