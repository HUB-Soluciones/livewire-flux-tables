<div class="space-y-4">
    @include('livewire-flux-tables::components.toolbar', [
        'component' => $component,
        'filters' => $filters,
    ])

    @include('livewire-flux-tables::components.table', [
        'component' => $component,
        'columns' => $columns,
        'rows' => $rows,
        'sticky' => $sticky,
    ])

    @include('livewire-flux-tables::components.pagination', [
        'rows' => $rows,
    ])
</div>
