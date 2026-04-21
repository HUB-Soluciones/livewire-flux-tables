<?php

namespace HubSoluciones\LivewireFluxTables\Rendering;

use HubSoluciones\LivewireFluxTables\Columns\Column;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Stringable;

class CellRenderer
{
    public function render(Column $column, mixed $row, object $component): array
    {
        if ($view = $column->viewName()) {
            return [
                'html' => true,
                'value' => new HtmlString((string) view($view, [
                    'row' => $row,
                    'value' => $column->resolveValue($row),
                    'column' => $column,
                    'component' => $component,
                ])->render()),
            ];
        }

        $value = $column->formatValue($row, $component);

        if ($value instanceof HtmlString || $value instanceof Stringable) {
            return [
                'html' => true,
                'value' => new HtmlString((string) $value),
            ];
        }

        return [
            'html' => $column->allowsHtml(),
            'value' => $value,
        ];
    }
}
