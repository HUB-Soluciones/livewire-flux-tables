<div class="{{ config('livewire-flux-tables.table_wrapper_class') }}">
    <div class="{{ config('livewire-flux-tables.table_scroll_class') }}">
        <table class="min-w-full border-separate border-spacing-0 text-sm text-zinc-700 dark:text-zinc-300">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-800">
                    @foreach ($columns as $index => $column)
                        @php($meta = $sticky[$index] ?? ['class' => '', 'header_class' => '', 'style' => '', 'is_sticky' => false])

                        @if ($column->isSelectionColumn())
                            <th scope="col" style="{{ $meta['style'] }}" class="border-b border-zinc-200 dark:border-zinc-700 px-4 py-3 {{ trim($meta['header_class']) }}">
                                @include('livewire-flux-tables::components.selection-header', [
                                    'component' => $component,
                                    'column' => $column,
                                ])
                            </th>
                        @elseif ($column->isSortable())
                            <th
                                scope="col"
                                style="{{ $meta['style'] }}"
                                class="border-b border-zinc-200 dark:border-zinc-700 px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400 {{ trim($meta['header_class']) }} {{ $column->isMobileHidden() ? 'hidden md:table-cell' : 'table-cell' }}"
                            >
                                <button
                                    type="button"
                                    wire:click="sortBy('{{ $column->field() }}')"
                                    class="inline-flex items-center gap-2 font-inherit text-left text-current"
                                >
                                    <span>{{ $column->label() }}</span>

                                    @if ($component->sort === $column->field())
                                        <span class="text-zinc-900 dark:text-zinc-100">{{ $component->direction === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-zinc-400 dark:text-zinc-500">↕</span>
                                    @endif
                                </button>
                            </th>
                        @else
                            <th
                                scope="col"
                                style="{{ $meta['style'] }}"
                                class="border-b border-zinc-200 dark:border-zinc-700 px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400 {{ trim($meta['header_class']) }} {{ $column->isMobileHidden() ? 'hidden md:table-cell' : 'table-cell' }}"
                            >
                                {{ $column->label() }}
                            </th>
                        @endif
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse ($rows as $row)
                    @php($rowSelected = $component->hasSelection() && $component->isRowSelected($row))
                    @php($rowBgClass = $component->rowBackgroundClass($loop->iteration, $rowSelected))
                    <tr class="border-b border-zinc-100 dark:border-zinc-800 last:border-b-0 transition {{ $rowBgClass }} {{ $rowSelected ? 'hover:bg-blue-50 dark:hover:bg-blue-900/30' : 'hover:bg-zinc-100/60 dark:hover:bg-zinc-800/60' }}">
                        @foreach ($columns as $index => $column)
                            @php($meta = $sticky[$index] ?? ['class' => '', 'header_class' => '', 'style' => '', 'is_sticky' => false])
                            <td
                                style="{{ $meta['style'] }}"
                                class="border-b border-zinc-100 dark:border-zinc-800 px-4 py-4 align-top {{ trim($meta['class']) }} {{ ($meta['is_sticky'] ?? false) ? $rowBgClass : '' }} {{ $column->isMobileHidden() ? 'hidden md:table-cell' : 'table-cell' }}"
                            >
                                @if ($column->isSelectionColumn())
                                    @include('livewire-flux-tables::components.selection-cell', [
                                        'component' => $component,
                                        'column' => $column,
                                        'row' => $row,
                                    ])
                                @else
                                    @include('livewire-flux-tables::components.cell', [
                                        'component' => $component,
                                        'column' => $column,
                                        'row' => $row,
                                    ])
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="px-4 py-12">
                            @include('livewire-flux-tables::components.empty-state', [
                                'component' => $component,
                            ])
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
