<div class="flex w-full items-center gap-2 md:hidden" role="group" aria-label="{{ __('Sort records') }}">
    <label for="{{ $table->getId() }}-mobile-sort" class="sr-only">{{ __('Sort by') }}</label>
    <select
        id="{{ $table->getId() }}-mobile-sort"
        wire:change="setSortField($event.target.value)"
        class="min-w-0 flex-1 rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200 data-loading:pointer-events-none data-loading:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-700"
    >
        <option value="">{{ __('Sort by') }}</option>
        @foreach ($mobileSortColumns as $column)
            <option value="{{ $column->field() }}" @selected($table->sort === $column->field())>{{ $column->label() }}</option>
        @endforeach
    </select>

    @include('livewire-flux-tables::components.mobile-sort-direction', ['table' => $table])
</div>
