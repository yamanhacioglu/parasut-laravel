<?php

namespace Northlab\Parasut\Tests;

use Northlab\Parasut\Support\QueryParameters;
use PHPUnit\Framework\TestCase;

class QueryParametersTest extends TestCase
{
    public function test_builds_filter_sort_and_page_params(): void
    {
        $query = QueryParameters::make()
            ->filter('name', 'Ahmet')
            ->sort('-created_at')
            ->page(2, 25)
            ->include(['category', 'tags'])
            ->toArray();

        $this->assertSame('Ahmet', $query['filter[name]']);
        $this->assertSame('-created_at', $query['sort']);
        $this->assertSame(2, $query['page[number]']);
        $this->assertSame(25, $query['page[size]']);
        $this->assertSame('category,tags', $query['include']);
    }

    public function test_from_array_builds_equivalent_query(): void
    {
        $query = QueryParameters::fromArray([
            'filter' => ['email' => 'a@b.com'],
            'sort' => 'name',
            'page' => ['number' => 1, 'size' => 15],
            'include' => 'category',
        ]);

        $this->assertSame('a@b.com', $query['filter[email]']);
        $this->assertSame('name', $query['sort']);
        $this->assertSame(1, $query['page[number]']);
        $this->assertSame(15, $query['page[size]']);
        $this->assertSame('category', $query['include']);
    }

    public function test_empty_filter_values_are_ignored(): void
    {
        $query = QueryParameters::make()
            ->filter('name', '')
            ->filter('email', null)
            ->toArray();

        $this->assertArrayNotHasKey('filter[name]', $query);
        $this->assertArrayNotHasKey('filter[email]', $query);
    }
}
