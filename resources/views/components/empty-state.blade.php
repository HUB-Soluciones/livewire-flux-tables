<div class="rounded-3xl border border-dashed border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-6 py-12 text-center">
    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">
        {{ $table->emptyStateHeading() }}
    </p>

    <p class="mx-auto mt-3 max-w-xl text-sm text-zinc-500 dark:text-zinc-400">
        {{ $table->emptyStateMessage() }}
    </p>
</div>
