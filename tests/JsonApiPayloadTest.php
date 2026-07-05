<?php

namespace Northlab\Parasut\Tests;

use Northlab\Parasut\Support\JsonApiPayload;
use PHPUnit\Framework\TestCase;

class JsonApiPayloadTest extends TestCase
{
    public function test_make_builds_basic_payload(): void
    {
        $payload = JsonApiPayload::make('contacts', ['name' => 'Ahmet']);

        $this->assertSame([
            'data' => [
                'type' => 'contacts',
                'attributes' => ['name' => 'Ahmet'],
            ],
        ], $payload);
    }

    public function test_make_includes_id_for_updates(): void
    {
        $payload = JsonApiPayload::make('contacts', ['name' => 'Ahmet'], [], 42);

        $this->assertSame('42', $payload['data']['id']);
    }

    public function test_make_formats_to_one_relationship(): void
    {
        $payload = JsonApiPayload::make('sales_invoices', ['description' => 'Test'], [
            'contact' => JsonApiPayload::ref('contacts', 5),
        ]);

        $this->assertSame([
            'data' => ['id' => '5', 'type' => 'contacts'],
        ], $payload['data']['relationships']['contact']);
    }

    public function test_make_formats_to_many_relationship(): void
    {
        $payload = JsonApiPayload::make('sales_invoices', [], [
            'tags' => JsonApiPayload::refs('tags', [1, 2, 3]),
        ]);

        $this->assertCount(3, $payload['data']['relationships']['tags']['data']);
        $this->assertSame(['id' => '1', 'type' => 'tags'], $payload['data']['relationships']['tags']['data'][0]);
    }

    public function test_nested_builds_compound_resource_with_relationships(): void
    {
        $detail = JsonApiPayload::nested('sales_invoice_details', [
            'quantity' => 2,
            'unit_price' => 100,
        ], [
            'product' => JsonApiPayload::ref('products', 9),
        ]);

        $this->assertSame('sales_invoice_details', $detail['type']);
        $this->assertSame(2, $detail['attributes']['quantity']);
        $this->assertSame(['data' => ['id' => '9', 'type' => 'products']], $detail['relationships']['product']);
    }
}
