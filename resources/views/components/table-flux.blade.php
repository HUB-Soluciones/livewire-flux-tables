<div class="{{ config('livewire-flux-tables.table_wrapper_class') }} {{ config('livewire-flux-tables.table_edge_padding_class', '[&_th:first-child]:ps-4 [&_th:last-child]:pe-4 [&_td:first-child]:ps-4 [&_td:last-child]:pe-4') }} {{ $table->mobileLayout() === 'cards' ? 'hidden md:block' : '' }}">
    <flux:table>
        <flux:table.columns sticky>
            @foreach ($columns as $index => $column)
                @php($meta = $sticky[$index] ?? ['class' => '', 'header_class' => '', 'style' => '', 'is_sticky' => false])
                @php($sortAction = $column->isSortable() ? "sortBy('{$column->field()}')" : '')
                <flux:table.column
                    style="{{ $meta['style'] }}"
                    :align="$column->alignment() === 'right' ? 'end' : ($column->alignment() === 'center' ? 'center' : 'start')"
                    :sortable="$column->isSortable()"
                    :sorted="$table->sort === $column->field()"
                    :direction="$table->sort === $column->field() ? $table->direction : null"
                    :sticky="$column->stickyPosition() === 'left'"
                    wire:click="{{ $sortAction }}"
                    class="{{ trim($meta['header_class']) }} data-loading:pointer-events-none data-loading:opacity-50"
                >
                    @if ($column->isSelectionColumn())
                        @include('livewire-flux-tables::components.selection-header', ['table' => $table, 'column' => $column])
                    @else
                        {{ $column->label() }}
                    @endif
                </flux:table.column>
            @endforeach
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($rows as $row)
                @php($rowSelected = $table->hasSelection() && $table->isRowSelected($row))
                <flux:table.row :key="$table->resolveRowKey($row)" class="{{ $table->rowBackgroundClass($loop->iteration, $rowSelected) }}">
                    @foreach ($columns as $index => $column)
                        @php($meta = $sticky[$index] ?? ['class' => '', 'style' => '', 'is_sticky' => false])
                        <flux:table.cell
                            style="{{ $meta['style'] }}"
                            class="{{ trim($meta['class']) }} {{ ($meta['is_sticky'] ?? false) ? $table->rowBackgroundClass($loop->parent->iteration, $rowSelected) : '' }}"
                            :align="$column->alignment() === 'right' ? 'end' : ($column->alignment() === 'center' ? 'center' : 'start')"
                            :sticky="$column->stickyPosition() === 'left'"
                        >
                            @if ($column->isSelectionColumn())
                                @include('livewire-flux-tables::components.selection-cell', ['table' => $table, 'column' => $column, 'row' => $row])
                            @else
                                @include('livewire-flux-tables::components.cell', ['table' => $table, 'column' => $column, 'row' => $row])
                            @endif
                        </flux:table.cell>
                    @endforeach
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="{{ count($columns) }}">
                        @include('livewire-flux-tables::components.empty-state', ['table' => $table])
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
