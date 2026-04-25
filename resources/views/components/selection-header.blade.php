@php
    $selectionState = $component->pageSelectionState();
    $pageCount = count($component->getPageKeys());
    $totalCount = $component->totalRecords();
@endphp

<div x-data="{ open: false }" class="relative inline-flex items-center">
    <input
        type="checkbox"
        aria-label="{{ __('Select records') }}"
        @click.prevent="open = !open"
        {{ $selectionState === 'full' ? 'checked' : '' }}
        x-effect="$el.indeterminate = {{ $selectionState === 'partial' ? 'true' : 'false' }}"
        class="h-4 w-4 cursor-pointer rounded border-zinc-300 dark:border-zinc-600 text-blue-600 transition focus:ring-2 focus:ring-blue-500 focus:ring-offset-0 dark:focus:ring-offset-zinc-800"
    />

    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute left-0 top-full z-50 mt-1 min-w-[14rem] rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1 shadow-lg"
        style="display: none;"
    >
        <button
            type="button"
            wire:click="togglePageSelection"
            @click="open = false"
            class="block w-full px-4 py-2 text-left text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700"
        >
            {{ __('Select page (:count)', ['count' => $pageCount]) }}
        </button>

        @if ($totalCount !== null && $totalCount > $pageCount)
            <button
                type="button"
                wire:click="enableSelectAllRecords"
                @click="open = false"
                class="block w-full px-4 py-2 text-left text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700"
            >
                {{ __('Select all :count records', ['count' => $totalCount]) }}
            </button>
        @endif

        <button
            type="button"
            wire:click="clearSelection"
            @click="open = false"
            class="block w-full px-4 py-2 text-left text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700"
        >
            {{ __('Clear selection') }}
        </button>
    </div>
</div>
