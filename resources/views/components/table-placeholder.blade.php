<div
    role="status"
    aria-label="{{ __('Loading records') }}"
    class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900"
>
    <div class="h-10 w-full animate-pulse rounded-xl bg-zinc-100 motion-reduce:animate-none dark:bg-zinc-800"></div>
    @foreach (range(1, 4) as $row)
        <div class="grid grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] gap-4 py-2" aria-hidden="true">
            <div class="h-4 animate-pulse rounded bg-zinc-100 motion-reduce:animate-none dark:bg-zinc-800"></div>
            <div class="h-4 animate-pulse rounded bg-zinc-100 motion-reduce:animate-none dark:bg-zinc-800"></div>
        </div>
    @endforeach
    <span class="sr-only">{{ __('Loading records') }}</span>
</div>
