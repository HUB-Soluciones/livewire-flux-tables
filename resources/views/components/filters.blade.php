@if ($filters !== [])
    @php
        $filtersSize = $table->filtersSize();
        $widthClasses = [
            'sm' => 'sm:col-span-2 xl:col-span-2',
            'md' => 'sm:col-span-3 xl:col-span-3',
            'lg' => 'sm:col-span-6 xl:col-span-6',
            'full' => 'col-span-full',
        ];
    @endphp
    <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-6 xl:grid-cols-12">
        @foreach ($filters as $filter)
            <flux:field class="{{ $widthClasses[$filter->widthValue()] ?? $widthClasses['md'] }}">
                <flux:label :class="$filtersSize === 'sm' ? 'text-xs' : null">{{ $filter->label() }}</flux:label>

                @if ($filter->type() === 'select')
                    <flux:select
                        variant="listbox"
                        clearable
                        :size="$filtersSize"
                        :searchable="$filter->isSearchable()"
                        placeholder="{{ $filter->placeholderValue() ?: __('All') }}"
                        wire:model.live="tableFilters.{{ $filter->key() }}"
                    >
                        @foreach ($filter->optionsList() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @elseif ($filter->type() === 'date')
                    <flux:date-picker
                        clearable
                        :size="$filtersSize"
                        wire:model.live="tableFilters.{{ $filter->key() }}"
                    />
                @elseif ($filter->type() === 'date-range')
                    <flux:date-picker
                        mode="range"
                        clearable
                        :size="$filtersSize"
                        :with-presets="$filter->usesPresets()"
                        :presets="$filter->presetsValue()"
                        wire:model.live="tableDateRanges.{{ $filter->key() }}"
                    />
                @else
                    <flux:input
                        clearable
                        :size="$filtersSize"
                        placeholder="{{ $filter->placeholderValue() ?: $filter->label() }}"
                        wire:model.live.debounce.300ms="tableFilters.{{ $filter->key() }}"
                    />
                @endif
            </flux:field>
        @endforeach
    </div>
@endif
