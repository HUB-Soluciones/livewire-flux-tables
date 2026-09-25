<?php

namespace HubSoluciones\LivewireFluxTables\Filters;

use HubSoluciones\LivewireFluxTables\Contracts\AppliesToData;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class Filter implements AppliesToData
{
    /** Anchos válidos para el control del filtro en el panel de filtros. */
    public const WIDTHS = ['sm', 'md', 'lg', 'full'];

    protected mixed $applyCallback = null;

    protected mixed $default = null;

    protected ?string $placeholder = null;

    protected ?string $width = null;

    public function __construct(
        protected string $label,
        protected string $key,
    ) {
    }

    public static function make(string $label, string $key): static
    {
        return new static($label, $key);
    }

    public function label(): string
    {
        return $this->label;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function defaultValue(): mixed
    {
        return $this->default;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function placeholderValue(): ?string
    {
        return $this->placeholder;
    }

    /**
     * Ancho del control en el panel de filtros: 'sm', 'md', 'lg' o 'full'.
     * Sin definir, se usa `config('livewire-flux-tables.filter_default_width')`.
     */
    public function width(string $width): static
    {
        if (! in_array($width, self::WIDTHS, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid filter width [%s]. Expected one of: %s.', $width, implode(', ', self::WIDTHS))
            );
        }

        $this->width = $width;

        return $this;
    }

    public function small(): static
    {
        return $this->width('sm');
    }

    public function medium(): static
    {
        return $this->width('md');
    }

    public function large(): static
    {
        return $this->width('lg');
    }

    public function fullWidth(): static
    {
        return $this->width('full');
    }

    public function widthValue(): string
    {
        return $this->width ?? (string) config('livewire-flux-tables.filter_default_width', 'md');
    }

    public function applyUsing(callable $callback): static
    {
        $this->applyCallback = $callback;

        return $this;
    }

    public function initialState(): mixed
    {
        return $this->default;
    }

    public function apply(mixed $data, mixed $value, object $component): mixed
    {
        if (! $this->hasValue($value)) {
            return $data;
        }

        if ($this->applyCallback !== null) {
            call_user_func($this->applyCallback, $data, $value, $component, $this);

            return $data;
        }

        $method = 'apply'.Str::studly($this->key()).'Filter';

        if (method_exists($component, $method)) {
            $component->{$method}($data, $value);

            return $data;
        }

        $legacyMethod = 'filter'.Str::studly($this->key());

        if (method_exists($component, $legacyMethod)) {
            $component->{$legacyMethod}($data, $value);

            return $data;
        }

        return $this->applyDefault($data, $value);
    }

    public function normalizeForQueryString(mixed $value): mixed
    {
        return $value;
    }

    public function hasValue(mixed $value): bool
    {
        if (is_array($value)) {
            return collect($value)->filter(fn ($item) => $item !== null && $item !== '')->isNotEmpty();
        }

        return $value !== null && $value !== '';
    }

    public function applyToCollection(Collection $collection, mixed $value, callable $callback): Collection
    {
        return $collection->filter(fn ($row) => $callback($row, $value))->values();
    }

    abstract public function type(): string;

    abstract protected function applyDefault(mixed $data, mixed $value): mixed;
}
