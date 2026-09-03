<div class="space-y-4 has-[[data-loading]]:cursor-progress">
    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm">
        @include('livewire-flux-tables::components.toolbar', [
            'table' => $table,
            'filters' => $filters,
            'fluxPro' => $fluxPro ?? false,
            'mobileSortColumns' => $mobileSortColumns ?? $table->mobileSortColumns,
        ])
    </div>

    {{-- Banner de selección masiva --}}
    @if ($table->hasSelection() && count($table->selectedKeys) > 0)
        @php
            $selCol          = $table->selectionColumn();
            $bulkActionsRaw  = $selCol->getNormalizedBulkActions();
            $totalRecords    = method_exists($rows, 'total') ? $rows->total() : count($table->selectedKeys);
            $selectedCount   = $table->selectAllRecords ? $totalRecords : count($table->selectedKeys);
            $resourceWord    = $selCol->getResourceLabel($selectedCount);
            $bannerClass     = config('livewire-flux-tables.selection_banner_class');
            $bannerTextClass = config('livewire-flux-tables.selection_banner_text_class');
            $bannerLinkClass = config('livewire-flux-tables.selection_banner_link_class');
            $hasMorePages    = method_exists($rows, 'total') && $rows->total() > count($rows->items());
        @endphp

        <div class="{{ $bannerClass }}" wire:transition>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 min-w-0">
                @if ($table->selectAllRecords)
                    <span class="{{ $bannerTextClass }}">
                        {{ __('All :count :resource are selected.', ['count' => $totalRecords, 'resource' => $resourceWord]) }}
                    </span>
                    <button type="button" wire:click="clearSelection" class="{{ $bannerLinkClass }}">
                        {{ __('Clear selection') }}
                    </button>
                @else
                    <span class="{{ $bannerTextClass }}">
                        {{ __(':count :resource selected on this page.', ['count' => count($table->selectedKeys), 'resource' => $resourceWord]) }}
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
                <div class="flex flex-wrap items-center gap-1.5">
                    @foreach ($bulkActionsRaw as $method => $action)
                        <flux:button
                            type="button"
                            wire:click="{{ $method }}"
                            size="sm"
                            :variant="match ($action['variant'] ?? null) {
                                'primary' => 'primary',
                                'danger' => 'danger',
                                default => 'outline',
                            }"
                            :icon="$action['icon'] ?: null"
                        >
                            {{ __($action['label']) }}
                        </flux:button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @include('livewire-flux-tables::components.table', [
        'table' => $table,
        'columns' => $columns,
        'rows' => $rows,
        'sticky' => $sticky,
        'fluxPro' => $fluxPro ?? false,
    ])

    @if ($table->mobileLayout() === 'cards')
        @include('livewire-flux-tables::components.mobile-cards', [
            'table' => $table,
            'rows' => $rows,
            'mobileSummary' => $mobileSummary ?? $table->mobileSummaryColumns(),
            'mobileDetails' => $mobileDetails ?? $table->mobileDetailColumns(),
        ])
    @endif

    @include('livewire-flux-tables::components.pagination', [
        'rows' => $rows,
    ])
</div>
