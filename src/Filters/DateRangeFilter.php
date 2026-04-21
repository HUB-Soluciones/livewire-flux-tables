<?php

namespace HubSoluciones\LivewireFluxTables\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class DateRangeFilter extends Filter
{
    public function initialState(): mixed
    {
        return $this->default ?? [
            'from' => null,
            'to' => null,
        ];
    }

    public function type(): string
    {
        return 'date-range';
    }

    public function hasValue(mixed $value): bool
    {
        return is_array($value) && (($value['from'] ?? null) || ($value['to'] ?? null));
    }

    protected function applyDefault(mixed $data, mixed $value): mixed
    {
        $from = $value['from'] ?? null;
        $to = $value['to'] ?? null;

        if ($data instanceof EloquentBuilder || $data instanceof QueryBuilder) {
            if ($from) {
                $data->whereDate($this->key(), '>=', $from);
            }

            if ($to) {
                $data->whereDate($this->key(), '<=', $to);
            }

            return $data;
        }

        if ($data instanceof Collection) {
            return $this->applyToCollection($data, $value, function ($row, $range) {
                $date = substr((string) data_get($row, $this->key(), ''), 0, 10);

                if (($range['from'] ?? null) && $date < $range['from']) {
                    return false;
                }

                if (($range['to'] ?? null) && $date > $range['to']) {
                    return false;
                }

                return true;
            });
        }

        return $data;
    }
}
