@if ($filters !== [])
    <div class="grid w-full gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($filters as $filter)
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500">
                    {{ $filter->label() }}
                </label>

                @if ($filter->type() === 'select')
                    <select
                        wire:model.live="tableFilters.{{ $filter->key() }}"
                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200"
                    >
                        <option value="">{{ $filter->placeholderValue() ?: __('All') }}</option>

                        @foreach ($filter->optionsList() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @elseif ($filter->type() === 'date')
                    <input
                        type="date"
                        wire:model.live="tableFilters.{{ $filter->key() }}"
                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200"
                    />
                @elseif ($filter->type() === 'date-range')
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <input
                            type="date"
                            wire:model.live="tableFilters.{{ $filter->key() }}.from"
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200"
                        />

                        <input
                            type="date"
                            wire:model.live="tableFilters.{{ $filter->key() }}.to"
                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200"
                        />
                    </div>
                @else
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="tableFilters.{{ $filter->key() }}"
                        placeholder="{{ $filter->placeholderValue() ?: $filter->label() }}"
                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200"
                    />
                @endif
            </div>
        @endforeach
    </div>
@endif
