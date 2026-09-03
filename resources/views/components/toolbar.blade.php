<div class="space-y-3">
    {{-- Active chips: sort + active filters --}}
    @php($chips = $table->activeChips())
    @if (!empty($chips))
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($chips as $chip)
                <span class="inline-flex items-center gap-1 rounded-full border border-zinc-300 dark:border-zinc-600 bg-zinc-100 dark:bg-zinc-800 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                    {{ $chip['label'] }}
                    @if ($chip['type'] === 'sort')
                        <button
                            type="button"
                            wire:click="clearSort"
                            class="ml-0.5 rounded-full p-0.5 text-zinc-500 dark:text-zinc-400 transition hover:bg-zinc-200 dark:hover:bg-zinc-700 hover:text-zinc-800 dark:hover:text-zinc-100"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="clearFilter('{{ $chip['key'] }}')"
                            class="ml-0.5 rounded-full p-0.5 text-zinc-500 dark:text-zinc-400 transition hover:bg-zinc-200 dark:hover:bg-zinc-700 hover:text-zinc-800 dark:hover:text-zinc-100"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @endif
                </span>
            @endforeach

            <flux:button
                type="button"
                wire:click="clearAll"
                variant="subtle"
                size="xs"
            >
                {{ __('Clear') }}
            </flux:button>
        </div>
    @endif

    {{-- Main toolbar row --}}
    <div class="flex flex-wrap items-center gap-2" data-flux-table-toolbar>
        {{-- Search --}}
        <div class="relative min-w-0 basis-full sm:basis-auto sm:flex-1 sm:max-w-xs" data-flux-table-search>
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon.magnifying-glass class="size-4 text-zinc-400 dark:text-zinc-500" />
            </div>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ $table->searchPlaceholder() }}"
                class="w-full rounded-xl border border-zinc-200 bg-white py-2.5 pl-9 pr-4 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 data-loading:opacity-60 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500 dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
            />
        </div>

        {{-- Filtros button --}}
        @if ($filters !== [])
            @php($activeFilterCount = $table->activeFiltersCount())
            <flux:button
                type="button"
                wire:click="toggleFilters"
                icon="funnel"
                :variant="$table->showFilters ? 'filled' : 'outline'"
                class="shrink-0"
            >
                {{ __('Filters') }}
                @if ($activeFilterCount > 0)
                    <flux:badge size="sm" color="blue">{{ $activeFilterCount }}</flux:badge>
                @endif
            </flux:button>
        @endif

        <div class="flex-1"></div>

        {{-- Orden móvil: el mismo estado se usa en los encabezados de escritorio. --}}
        @if ($table->mobileLayout() === 'cards' && !empty($mobileSortColumns ?? $table->mobileSortColumns))
            @include(
                ($fluxPro ?? false)
                    ? 'livewire-flux-tables::components.mobile-sort-pro'
                    : 'livewire-flux-tables::components.mobile-sort',
                ['table' => $table, 'mobileSortColumns' => $mobileSortColumns ?? $table->mobileSortColumns]
            )
        @endif

        {{-- Columnas --}}
        @php($hideableColumns = $table->hideableColumns)
        @if (!empty($hideableColumns))
            <flux:dropdown position="bottom" align="end" class="shrink-0">
                <flux:button
                    type="button"
                    variant="outline"
                    icon:trailing="chevron-down"
                >
                    {{ __('Columns') }}
                </flux:button>

                <flux:menu>
                    @foreach ($hideableColumns as $column)
                        <flux:menu.item
                            wire:click="toggleColumn('{{ $column->field() }}')"
                        >
                            <flux:checkbox
                                :checked="!in_array($column->field(), $table->hiddenColumns)"
                                class="pointer-events-none"
                            />
                            {{ $column->label() }}
                        </flux:menu.item>
                    @endforeach
                </flux:menu>
            </flux:dropdown>
        @endif

        {{-- Registros por página --}}
        <select
            wire:model.live="perPage"
            class="shrink-0 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 outline-none transition focus:border-zinc-400 dark:focus:border-zinc-500 focus:ring-2 focus:ring-zinc-100 dark:focus:ring-zinc-700"
        >
            @foreach ($table->perPageOptions() as $option)
                <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        </select>
    </div>

    {{-- Panel de filtros colapsable --}}
    @if ($table->showFilters && $filters !== [])
        <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3" wire:transition>
            @include('livewire-flux-tables::components.filters', [
                'table' => $table,
                'filters' => $filters,
            ])
        </div>
    @endif
</div>
