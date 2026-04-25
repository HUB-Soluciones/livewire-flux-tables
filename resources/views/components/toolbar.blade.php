<div class="space-y-3">
    {{-- Active chips: sort + active filters --}}
    @php($chips = $component->activeChips())
    @if (!empty($chips))
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($chips as $chip)
                <span class="inline-flex items-center gap-1 rounded-full border border-zinc-300 bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">
                    {{ $chip['label'] }}
                    @if ($chip['type'] === 'sort')
                        <button
                            type="button"
                            wire:click="clearSort"
                            class="ml-0.5 rounded-full p-0.5 text-zinc-500 transition hover:bg-zinc-200 hover:text-zinc-800"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="clearFilter('{{ $chip['key'] }}')"
                            class="ml-0.5 rounded-full p-0.5 text-zinc-500 transition hover:bg-zinc-200 hover:text-zinc-800"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @endif
                </span>
            @endforeach

            <button
                type="button"
                wire:click="clearAll"
                class="text-xs font-medium text-zinc-500 underline-offset-2 transition hover:text-zinc-800 hover:underline"
            >
                {{ __('Clear') }}
            </button>
        </div>
    @endif

    {{-- Main toolbar row --}}
    <div class="flex flex-wrap items-center gap-2">
        {{-- Search --}}
        <div class="relative min-w-0 flex-1 sm:max-w-xs">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ $component->searchPlaceholder() }}"
                class="w-full rounded-xl border border-zinc-200 bg-white py-2.5 pl-9 pr-4 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100"
            />
        </div>

        {{-- Filtros button --}}
        @if ($filters !== [])
            @php($activeFilterCount = $component->activeFiltersCount())
            <button
                type="button"
                wire:click="toggleFilters"
                class="inline-flex shrink-0 items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-medium transition {{ $component->showFilters ? 'border-blue-300 bg-blue-50 text-blue-700' : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50' }}"
            >
                {{ __('Filters') }}
                @if ($activeFilterCount > 0)
                    <span class="flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-blue-500 px-1 text-[10px] font-bold text-white">
                        {{ $activeFilterCount }}
                    </span>
                @endif
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
            </button>
        @endif

        <div class="flex-1"></div>

        {{-- Acciones masivas --}}
        @if ($component->hasSelection() && ($selectionColumn = $component->selectionColumn()))
            @php($bulkActions = $selectionColumn->getBulkActions())
            @if (!empty($bulkActions))
                <div x-data="{ open: false }" class="relative shrink-0">
                    <button
                        type="button"
                        @click="open = !open"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                        :class="{ 'opacity-60': {{ count($component->selectedKeys) === 0 ? 'true' : 'false' }} }"
                    >
                        {{ __('Bulk actions') }}
                        @if (count($component->selectedKeys) > 0)
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-600">
                                {{ count($component->selectedKeys) }}
                            </span>
                        @endif
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        @click.outside="open = false"
                        class="absolute right-0 z-50 mt-1.5 min-w-[11rem] origin-top-right rounded-xl border border-zinc-200 bg-white py-1 shadow-lg"
                        style="display: none;"
                    >
                        @foreach ($bulkActions as $method => $label)
                            <button
                                type="button"
                                wire:click="{{ $method }}"
                                @click="open = false"
                                {{ count($component->selectedKeys) === 0 ? 'disabled' : '' }}
                                class="flex w-full items-center px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- Columnas --}}
        @php($hideableColumns = $component->hideableColumns())
        @if (!empty($hideableColumns))
            <div x-data="{ open: false }" class="relative shrink-0">
                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                >
                    {{ __('Columns') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    @click.outside="open = false"
                    class="absolute right-0 z-50 mt-1.5 min-w-[13rem] origin-top-right rounded-xl border border-zinc-200 bg-white py-1 shadow-lg"
                    style="display: none;"
                >
                    @foreach ($hideableColumns as $column)
                        <label class="flex cursor-pointer items-center gap-3 px-4 py-2.5 hover:bg-zinc-50">
                            <input
                                type="checkbox"
                                wire:click="toggleColumn('{{ $column->field() }}')"
                                {{ !in_array($column->field(), $component->hiddenColumns) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-zinc-300 text-blue-600 transition focus:ring-2 focus:ring-blue-500 focus:ring-offset-0"
                            />
                            <span class="text-sm text-zinc-700">{{ $column->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Registros por página --}}
        <select
            wire:model.live="perPage"
            class="shrink-0 rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100"
        >
            @foreach ($component->perPageOptions() as $option)
                <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        </select>
    </div>

    {{-- Panel de filtros colapsable --}}
    @if ($component->showFilters && $filters !== [])
        <div class="border-t border-zinc-100 pt-3">
            @include('livewire-flux-tables::components.filters', [
                'component' => $component,
                'filters' => $filters,
            ])
        </div>
    @endif
</div>
