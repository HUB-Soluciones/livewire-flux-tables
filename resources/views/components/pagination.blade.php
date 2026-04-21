@if (method_exists($rows, 'hasPages') && $rows->hasPages())
    <div class="rounded-3xl border border-zinc-200 bg-white px-4 py-3 shadow-sm">
        {{ $rows->onEachSide(1)->links() }}
    </div>
@endif
