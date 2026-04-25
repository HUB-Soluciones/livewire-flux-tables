<?php

namespace HubSoluciones\LivewireFluxTables\Rendering;

use HubSoluciones\LivewireFluxTables\Columns\Column;

class StickyColumnManager
{
    public function map(array $columns, string $defaultWidth = '12rem'): array
    {
        $leftOffset = '0px';
        $rightOffset = '0px';
        $metadata = [];

        foreach ($columns as $index => $column) {
            if (! $column instanceof Column) {
                continue;
            }

            $metadata[$index] = [
                'style' => $column->widthValue() ? 'width: '.$column->widthValue().';' : '',
                'class' => $this->alignmentClass($column),
                'header_class' => $this->alignmentClass($column),
                'is_sticky' => false,
            ];
        }

        foreach ($columns as $index => $column) {
            if (! $column instanceof Column || ! $column->isSticky() || $column->stickyPosition() !== 'left') {
                continue;
            }

            $width = $column->widthValue() ?: $defaultWidth;

            $metadata[$index]['style'] .= ' position: sticky; left: '.$leftOffset.';';
            $metadata[$index]['is_sticky'] = true;
            $metadata[$index]['class'] .= ' sticky shadow-[6px_0_12px_-12px_rgba(15,23,42,0.35)] z-20';
            $metadata[$index]['header_class'] .= ' sticky shadow-[6px_0_12px_-12px_rgba(15,23,42,0.35)] z-30 '.config('livewire-flux-tables.sticky_header_class', 'bg-zinc-50 dark:bg-zinc-800');

            $leftOffset = 'calc('.$leftOffset.' + '.$width.')';
        }

        for ($index = count($columns) - 1; $index >= 0; $index--) {
            $column = $columns[$index];

            if (! $column instanceof Column || ! $column->isSticky() || $column->stickyPosition() !== 'right') {
                continue;
            }

            $width = $column->widthValue() ?: $defaultWidth;

            $metadata[$index]['style'] .= ' position: sticky; right: '.$rightOffset.';';
            $metadata[$index]['is_sticky'] = true;
            $metadata[$index]['class'] .= ' sticky shadow-[-6px_0_12px_-12px_rgba(15,23,42,0.35)] z-20';
            $metadata[$index]['header_class'] .= ' sticky shadow-[-6px_0_12px_-12px_rgba(15,23,42,0.35)] z-30 '.config('livewire-flux-tables.sticky_header_class', 'bg-zinc-50 dark:bg-zinc-800');

            $rightOffset = 'calc('.$rightOffset.' + '.$width.')';
        }

        return $metadata;
    }

    protected function alignmentClass(Column $column): string
    {
        return match ($column->alignment()) {
            'center' => ' text-center',
            'right' => ' text-right',
            default => ' text-left',
        };
    }
}
