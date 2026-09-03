@php($cell = $table->renderCell($column, $row))

<div class="{{ $column->shouldStackOnMobile() ? 'grid gap-1 md:block' : '' }}">
    @if ($column->shouldStackOnMobile() || $column->mobileLabelValue())
        <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 md:hidden">
            {{ $column->mobileLabelValue() ?: $column->label() }}
        </span>
    @endif

    @if ($cell['html'])
        {!! $cell['value'] !!}
    @else
        {{ $cell['value'] }}
    @endif
</div>
