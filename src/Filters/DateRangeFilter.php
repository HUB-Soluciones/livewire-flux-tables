<?php

namespace HubSoluciones\LivewireFluxTables\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class DateRangeFilter extends Filter
{
    protected bool $withPresets = true;

    /** Space-separated `Flux\DateRangePreset` values, or null to use Flux's defaults. */
    protected ?string $presets = null;

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

    /**
     * Restrict the `flux:date-picker` presets list. Accepts either a
     * space-separated string or an array of `Flux\DateRangePreset` values
     * (e.g. `today`, `last7Days`, `thisMonth`, `yearToDate`, `allTime`).
     */
    public function presets(string|array $presets): static
    {
        $this->presets = is_array($presets) ? implode(' ', $presets) : $presets;

        return $this;
    }

    public function presetsValue(): ?string
    {
        return $this->presets;
    }

    /** Disable the presets column on the `flux:date-picker` (enabled by default). */
    public function withoutPresets(): static
    {
        $this->withPresets = false;

        return $this;
    }

    public function usesPresets(): bool
    {
        return $this->withPresets;
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
