<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * Alis Faturasi (Purchase Bill). Parasut API iki farkli olusturma modu sunar:
 * - Basic: kalemsiz, tek tutarli basit fatura
 * - Detailed: urun/depo kalemleriyle detayli fatura
 */
class PurchaseBillResource extends BaseResource
{
    protected string $endpoint = 'purchase_bills';

    protected ?string $jsonApiType = 'purchase_bills';

    /**
     * Kalemsiz basit alis faturasi olusturur (PurchaseBillBasicFormAttributes).
     */
    public function createBasic(array $attributes, int|string $contactId, array $extraRelationships = [], array $include = []): array
    {
        $relationships = ['supplier' => JsonApiPayload::ref('contacts', $contactId)];
        $relationships = array_merge($relationships, $this->mapExtraRelationships($extraRelationships));

        return $this->create($attributes, $relationships, $include);
    }

    /**
     * Urun/depo kalemleriyle detayli alis faturasi olusturur.
     *
     * @param  array  $details  Her biri: ['quantity'=>, 'unit_price'=>, 'vat_rate'=>, 'product_id'=>, 'warehouse_id'=>, ...]
     */
    public function createDetailed(array $attributes, int|string $contactId, array $details, array $extraRelationships = [], array $include = []): array
    {
        $relationships = [
            'supplier' => JsonApiPayload::ref('contacts', $contactId),
            'details' => array_map([$this, 'buildDetail'], $details),
        ];
        $relationships = array_merge($relationships, $this->mapExtraRelationships($extraRelationships));

        return $this->create($attributes, $relationships, $include);
    }

    protected function mapExtraRelationships(array $extra): array
    {
        $relationships = [];

        if (isset($extra['category'])) {
            $relationships['category'] = JsonApiPayload::ref('item_categories', $extra['category']);
        }

        if (isset($extra['tags'])) {
            $relationships['tags'] = JsonApiPayload::refs('tags', $extra['tags']);
        }

        if (isset($extra['spender'])) {
            $relationships['spender'] = JsonApiPayload::ref('employees', $extra['spender']);
        }

        return $relationships;
    }

    protected function buildDetail(array $detail): array
    {
        $productId = $detail['product_id'] ?? null;
        $warehouseId = $detail['warehouse_id'] ?? null;
        unset($detail['product_id'], $detail['warehouse_id']);

        $relationships = [];

        if ($productId) {
            $relationships['product'] = JsonApiPayload::ref('products', $productId);
        }

        if ($warehouseId) {
            $relationships['warehouse'] = JsonApiPayload::ref('warehouses', $warehouseId);
        }

        return JsonApiPayload::nested('purchase_bill_details', $detail, $relationships);
    }

    /**
     * Faturaya odeme kaydi ekler.
     */
    public function pay(int|string $billId, array $attributes): array
    {
        $payload = JsonApiPayload::make('payments', $attributes);

        return $this->postAction($billId, 'payments', $payload);
    }

    public function archive(int|string $billId): array
    {
        return $this->patchAction($billId, 'archive');
    }

    public function unarchive(int|string $billId): array
    {
        return $this->patchAction($billId, 'unarchive');
    }

    public function cancel(int|string $billId): array
    {
        return $this->deleteAction($billId, 'cancel');
    }

    public function recover(int|string $billId): array
    {
        return $this->patchAction($billId, 'recover');
    }
}
