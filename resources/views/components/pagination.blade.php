@if (method_exists($rows, 'hasPages') && $rows->hasPages())
    <div class="rounded-3xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-3 shadow-sm">
        {{ $rows->onEachSide(1)->links() }}
    </div>
@endif
