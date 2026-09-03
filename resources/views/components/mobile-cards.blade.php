@php($summaryColumns = $mobileSummary ?? $table->mobileSummaryColumns())
@php($detailColumns = $mobileDetails ?? $table->mobileDetailColumns())

<div class="space-y-3 md:hidden" x-data="{ expanded: null }">
    @if ($table->hasSelection())
        @php($selectionState = $table->pageSelectionState())
        @php($pageCount = count($table->pageKeys()))
        @php($totalCount = $table->totalRecords())
        <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-zinc-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900">
            <button
                type="button"
                wire:click="togglePageSelection"
                class="inline-flex min-h-11 items-center gap-2 rounded-xl px-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500 data-loading:pointer-events-none data-loading:opacity-50 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                <span aria-hidden="true" class="inline-flex h-4 w-4 items-center justify-center rounded border border-zinc-300 text-blue-600 dark:border-zinc-600">
                    @if ($selectionState === 'full')
                        <svg viewBox="0 0 16 16" fill="none" class="h-3 w-3" stroke="currentColor" stroke-width="2.5">
                            <path d="m3 8 3 3 7-7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    @elseif ($selectionState === 'partial')
                        <svg viewBox="0 0 16 16" fill="none" class="h-3 w-3" stroke="currentColor" stroke-width="2.5">
                            <path d="M3 8h10" stroke-linecap="round" />
                        </svg>
                    @endif
                </span>
                {{ __('Select page (:count)', ['count' => $pageCount]) }}
            </button>
            @if ($selectionState !== 'none')
                <button
                    type="button"
                    wire:click="clearSelection"
                    class="min-h-11 rounded-xl px-3 text-sm font-medium text-blue-700 underline-offset-2 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 data-loading:pointer-events-none data-loading:opacity-50 dark:text-blue-300"
                >{{ __('Clear selection') }}</button>
            @endif
            @if ($totalCount !== null && $totalCount > $pageCount && ! $table->selectAllRecords)
                <button
                    type="button"
                    wire:click="enableSelectAllRecords"
                    class="min-h-11 rounded-xl px-3 text-sm font-medium text-blue-700 underline-offset-2 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 data-loading:pointer-events-none data-loading:opacity-50 dark:text-blue-300"
                >{{ __('Select all :count records', ['count' => $totalCount]) }}</button>
            @endif
        </div>
    @endif

    @forelse ($rows as $row)
        @php($rowKey = (string) $table->resolveRowKey($row))
        @php($rowSelected = $table->hasSelection() && $table->isRowSelected($row))
        @php($rowId = $table->mobileRowId($row))
        <div wire:key="{{ $rowId }}" x-data="{ rowKey: @js($rowKey) }">
            <flux:card
                role="article"
                class="overflow-hidden p-0! transition {{ $rowSelected ? 'ring-2 ring-blue-500/30' : '' }}"
            >
            <div class="flex items-start gap-3 p-4">
                @if ($table->hasSelection())
                    @include('livewire-flux-tables::components.selection-cell', [
                        'table' => $table,
                        'column' => $table->selectionColumn(),
                        'row' => $row,
                        'mobile' => true,
                    ])
                @endif

                <div class="min-w-0 flex-1 space-y-2">
                    @foreach ($summaryColumns as $column)
                        @php($cell = $table->renderCell($column, $row))
                        <div class="min-w-0 {{ $loop->first ? 'text-base font-semibold text-zinc-900 dark:text-white' : 'flex items-center justify-between gap-3 text-sm' }}">
                            @if (! $loop->first)
                                <span class="shrink-0 text-xs font-medium uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">
                                    {{ $column->mobileLabelValue() ?: $column->label() }}
                                </span>
                            @endif
                            <span class="min-w-0 break-words {{ $loop->first ? '' : 'text-right text-zinc-700 dark:text-zinc-200' }}">
                                @if ($cell['html']){!! $cell['value'] !!}@else{{ $cell['value'] }}@endif
                            </span>
                        </div>
                    @endforeach
                </div>

                @if ($detailColumns !== [])
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                        :aria-expanded="expanded === rowKey"
                        aria-controls="{{ $rowId }}-details"
                        :aria-label="expanded === rowKey ? @js(__('Hide details')) : @js(__('Show details'))"
                        @click="expanded = expanded === rowKey ? null : rowKey"
                    >
                        <svg class="h-5 w-5 transition-transform duration-200 motion-reduce:transition-none" :class="expanded === rowKey ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif
            </div>

            @if ($detailColumns !== [])
                <div
                    id="{{ $rowId }}-details"
                    x-show="expanded === rowKey"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150 motion-reduce:transition-none"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100 motion-reduce:transition-none"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="border-t border-zinc-100 px-4 pb-4 pt-3 dark:border-zinc-800"
                >
                    <dl class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($detailColumns as $column)
                            @php($cell = $table->renderCell($column, $row))
                            <div class="grid grid-cols-[minmax(0,40%)_minmax(0,1fr)] gap-3 py-3 first:pt-0 last:pb-0">
                                <dt class="text-xs font-medium uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400">
                                    {{ $column->mobileLabelValue() ?: $column->label() }}
                                </dt>
                                <dd class="min-w-0 break-words text-right text-sm text-zinc-700 dark:text-zinc-200">
                                    @if ($cell['html']){!! $cell['value'] !!}@else{{ $cell['value'] }}@endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
            </flux:card>
        </div>
    @empty
        <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-12 dark:border-zinc-700 dark:bg-zinc-900">
            @include('livewire-flux-tables::components.empty-state', ['table' => $table])
        </div>
    @endforelse
</div>
