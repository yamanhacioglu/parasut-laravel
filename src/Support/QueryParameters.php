<?php

namespace Northlab\Parasut\Support;

/**
 * Parasut listeleme uçlarindaki standart query parametrelerini
 * (filter[x], sort, page[number], page[size], include) uretir.
 */
class QueryParameters
{
    protected array $filters = [];

    protected ?string $sort = null;

    protected ?int $pageNumber = null;

    protected ?int $pageSize = null;

    protected array $include = [];

    public static function make(): static
    {
        return new static;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function filters(array $filters): static
    {
        $this->filters = array_merge($this->filters, array_filter(
            $filters,
            fn ($value) => $value !== null && $value !== ''
        ));

        return $this;
    }

    public function filter(string $key, mixed $value): static
    {
        if ($value !== null && $value !== '') {
            $this->filters[$key] = $value;
        }

        return $this;
    }

    public function sort(string $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    public function page(int $number, ?int $size = null): static
    {
        $this->pageNumber = $number;

        if ($size !== null) {
            $this->pageSize = $size;
        }

        return $this;
    }

    public function perPage(int $size): static
    {
        $this->pageSize = $size;

        return $this;
    }

    /**
     * @param  string[]|string  $relations
     */
    public function include(array|string $relations): static
    {
        $this->include = array_merge($this->include, (array) $relations);

        return $this;
    }

    public function toArray(): array
    {
        $query = [];

        foreach ($this->filters as $key => $value) {
            $query["filter[{$key}]"] = $value;
        }

        if ($this->sort) {
            $query['sort'] = $this->sort;
        }

        if ($this->pageNumber) {
            $query['page[number]'] = $this->pageNumber;
        }

        if ($this->pageSize) {
            $query['page[size]'] = $this->pageSize;
        }

        if ($this->include !== []) {
            $query['include'] = implode(',', $this->include);
        }

        return array_merge($query, $this->extra);
    }

    /**
     * Bir dizi (ornek: ['filter' => ['name' => 'X'], 'sort' => '-created_at', 'page' => ['number' => 1, 'size' => 25], 'include' => ['category']])
     * seklinde gelen esnek parametreyi query dizisine cevirir.
     */
    public static function fromArray(array $options): array
    {
        $builder = static::make();

        if (isset($options['filter']) && is_array($options['filter'])) {
            $builder->filters($options['filter']);
        }

        if (isset($options['sort'])) {
            $builder->sort($options['sort']);
        }

        if (isset($options['page']) && is_array($options['page'])) {
            $builder->page(
                $options['page']['number'] ?? 1,
                $options['page']['size'] ?? null
            );
        }

        if (isset($options['include'])) {
            $builder->include($options['include']);
        }

        // Ham/ozel query parametrelerine de izin ver (gecis kapisi).
        foreach ($options as $key => $value) {
            if (! in_array($key, ['filter', 'sort', 'page', 'include'], true)) {
                $builder->toArrayExtra($key, $value);
            }
        }

        return $builder->toArray();
    }

    protected array $extra = [];

    protected function toArrayExtra(string $key, mixed $value): void
    {
        $this->extra[$key] = $value;
    }
}
