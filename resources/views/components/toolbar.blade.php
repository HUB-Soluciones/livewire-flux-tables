<div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div class="w-full xl:max-w-md">
            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500">
                Buscar
            </label>

            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ $component->searchPlaceholder() }}"
                class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200"
            />
        </div>

        <div class="flex w-full flex-col gap-4 xl:items-end">
            @include('livewire-flux-tables::components.filters', [
                'component' => $component,
                'filters' => $filters,
            ])

            <div class="w-full sm:w-48">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500">
                    Registros por página
                </label>

                <select
                    wire:model.live="perPage"
                    class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200"
                >
                    @foreach ($component->perPageOptions() as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
