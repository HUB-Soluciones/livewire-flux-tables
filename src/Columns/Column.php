<?php

namespace HubSoluciones\LivewireFluxTables\Columns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;

class Column
{
    protected mixed $formatCallback = null;

    protected mixed $searchCallback = null;

    protected mixed $sortCallback = null;

    protected ?string $view = null;

    protected mixed $default = null;

    protected ?string $align = null;

    protected ?string $width = null;

    protected ?string $fixedPosition = null;

    protected ?string $mobileLabel = null;

    protected bool $sortable = false;

    protected bool $searchable = false;

    protected bool $mobileHidden = false;

    protected bool $stackOnMobile = false;

    protected bool $allowHtml = false;

    public function __construct(
        protected string $label,
        protected ?string $field = null,
    ) {
        $this->field ??= Str::snake($label);
    }

    public static function make(string $label, ?string $field = null): static
    {
        return new static($label, $field);
    }

    public function label(): string
    {
        return $this->label;
    }

    public function field(): string
    {
        return $this->field ?? '';
    }

    public function key(): string
    {
        return str_replace('.', '_', $this->field());
    }

    public function sortable(?callable $callback = null): static
    {
        $this->sortable = true;

        if ($callback !== null) {
            $this->sortCallback = $callback;
        }

        return $this;
    }

    public function searchable(?callable $callback = null): static
    {
        $this->searchable = true;

        if ($callback !== null) {
            $this->searchCallback = $callback;
        }

        return $this;
    }

    public function sortableUsing(callable $callback): static
    {
        $this->sortable = true;
        $this->sortCallback = $callback;

        return $this;
    }

    public function searchUsing(callable $callback): static
    {
        $this->searchable = true;
        $this->searchCallback = $callback;

        return $this;
    }

    public function sticky(string $position = 'left'): static
    {
        $this->fixedPosition = $position;

        return $this;
    }

    public function fixed(string $position = 'left'): static
    {
        return $this->sticky($position);
    }

    public function align(string $align): static
    {
        $this->align = $align;

        return $this;
    }

    public function width(string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function mobileHidden(bool $hidden = true): static
    {
        $this->mobileHidden = $hidden;

        return $this;
    }

    public function mobileLabel(?string $label): static
    {
        $this->mobileLabel = $label;

        return $this;
    }

    public function stackOnMobile(bool $stack = true): static
    {
        $this->stackOnMobile = $stack;

        return $this;
    }

    public function view(string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function format(callable $callback): static
    {
        $this->formatCallback = $callback;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function html(bool $allowHtml = true): static
    {
        $this->allowHtml = $allowHtml;

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isSticky(): bool
    {
        return $this->fixedPosition !== null;
    }

    public function stickyPosition(): ?string
    {
        return $this->fixedPosition;
    }

    public function alignment(): ?string
    {
        return $this->align;
    }

    public function widthValue(): ?string
    {
        return $this->width;
    }

    public function isMobileHidden(): bool
    {
        return $this->mobileHidden;
    }

    public function shouldStackOnMobile(): bool
    {
        return $this->stackOnMobile;
    }

    public function mobileLabelValue(): ?string
    {
        return $this->mobileLabel;
    }

    public function viewName(): ?string
    {
        return $this->view;
    }

    public function allowsHtml(): bool
    {
        return $this->allowHtml;
    }

    public function resolveValue(mixed $row): mixed
    {
        $value = data_get($row, $this->field());

        if ($value === null || $value === '') {
            return $this->default;
        }

        return $value;
    }

    public function formatValue(mixed $row, mixed $component = null): mixed
    {
        $value = $this->resolveValue($row);

        if ($this->formatCallback === null) {
            return $value;
        }

        return call_user_func($this->formatCallback, $value, $row, $this, $component);
    }

    public function applySearch(EloquentBuilder|QueryBuilder $query, string $term): void
    {
        if ($this->searchCallback !== null) {
            call_user_func($this->searchCallback, $query, $term, $this);

            return;
        }

        if (str_contains($this->field(), '.') && $query instanceof EloquentBuilder) {
            $relationship = Str::beforeLast($this->field(), '.');
            $field = Str::afterLast($this->field(), '.');

            $query->orWhereRelation($relationship, $field, 'like', '%'.$term.'%');

            return;
        }

        $query->orWhere($this->field(), 'like', '%'.$term.'%');
    }

    public function applySort(EloquentBuilder|QueryBuilder $query, string $direction): void
    {
        if ($this->sortCallback !== null) {
            call_user_func($this->sortCallback, $query, $direction, $this);

            return;
        }

        if (str_contains($this->field(), '.')) {
            return;
        }

        $query->orderBy($this->field(), $direction);
    }
}
