<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * Satis Faturasi (Sales Invoice). E-ticaret siparislerinden fatura
 * uretmek icin en cok kullanilan kaynak.
 */
class SalesInvoiceResource extends BaseResource
{
    protected string $endpoint = 'sales_invoices';

    protected ?string $jsonApiType = 'sales_invoices';

    /**
     * Kalemleri (details) ile birlikte satis faturasi olusturur.
     *
     * @param  array  $attributes  SalesInvoiceCreateUpdateAttributes (item_type, description,
     *                              issue_date, due_date, currency, ...)
     * @param  int|string  $contactId  Musteri ID
     * @param  array  $details  Her biri: ['quantity'=>, 'unit_price'=>, 'vat_rate'=>, 'discount_type'=>,
     *                          'discount_value'=>, 'product_id'=>, 'warehouse_id'=>, ...]
     * @param  array  $extraRelationships  ['category' => id, 'tags' => [id,...], 'sales_offer' => id]
     */
    public function createWithDetails(array $attributes, int|string $contactId, array $details = [], array $extraRelationships = [], array $include = []): array
    {
        $relationships = [
            'contact' => JsonApiPayload::ref('contacts', $contactId),
        ];

        if ($details !== []) {
            $relationships['details'] = array_map([$this, 'buildDetail'], $details);
        }

        if (isset($extraRelationships['category'])) {
            $relationships['category'] = JsonApiPayload::ref('item_categories', $extraRelationships['category']);
        }

        if (isset($extraRelationships['tags'])) {
            $relationships['tags'] = JsonApiPayload::refs('tags', $extraRelationships['tags']);
        }

        if (isset($extraRelationships['sales_offer'])) {
            $relationships['sales_offer'] = JsonApiPayload::ref('sales_offers', $extraRelationships['sales_offer']);
        }

        return $this->create($attributes, $relationships, $include);
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

        return JsonApiPayload::nested('sales_invoice_details', $detail, $relationships);
    }

    /**
     * Faturaya odeme/tahsilat kaydi ekler.
     *
     * @param  array  $attributes  ['description'=>, 'account_id'=>, 'date'=>, 'amount'=>, 'exchange_rate'=>]
     */
    public function pay(int|string $invoiceId, array $attributes): array
    {
        $payload = JsonApiPayload::make('payments', $attributes);

        return $this->postAction($invoiceId, 'payments', $payload);
    }

    public function archive(int|string $invoiceId): array
    {
        return $this->patchAction($invoiceId, 'archive');
    }

    public function unarchive(int|string $invoiceId): array
    {
        return $this->patchAction($invoiceId, 'unarchive');
    }

    /**
     * Faturayi iptal eder (silmez, "iptal" durumuna gecirir).
     */
    public function cancel(int|string $invoiceId): array
    {
        return $this->deleteAction($invoiceId, 'cancel');
    }

    /**
     * Iptal edilmis bir faturayi geri alir.
     */
    public function recover(int|string $invoiceId): array
    {
        return $this->patchAction($invoiceId, 'recover');
    }

    /**
     * Taslak/proforma faturayi kesin faturaya donusturur.
     */
    public function convertToInvoice(int|string $invoiceId): array
    {
        return $this->patchAction($invoiceId, 'convert_to_invoice');
    }
}
