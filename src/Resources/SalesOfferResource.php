<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * Satis Teklifi (Sales Offer). Siparis oncesi teklif/proforma sureci icin.
 */
class SalesOfferResource extends BaseResource
{
    protected string $endpoint = 'sales_offers';

    protected ?string $jsonApiType = 'sales_offers';

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

        return $this->create($attributes, $relationships, $include);
    }

    protected function buildDetail(array $detail): array
    {
        $productId = $detail['product_id'] ?? null;
        unset($detail['product_id']);

        $relationships = [];

        if ($productId) {
            $relationships['product'] = JsonApiPayload::ref('products', $productId);
        }

        return JsonApiPayload::nested('sales_offer_details', $detail, $relationships);
    }

    public function archive(int|string $offerId): array
    {
        return $this->patchAction($offerId, 'archive');
    }

    public function unarchive(int|string $offerId): array
    {
        return $this->patchAction($offerId, 'unarchive');
    }

    /**
     * Teklifin kalem (detail) listesini getirir.
     */
    public function details(int|string $offerId): array
    {
        return $this->getAction($offerId, 'details');
    }

    /**
     * Teklif PDF'i olusturur (asenkron). Donen "trackable_jobs" kaydinin
     * ID'sini TrackableJobResource::find() ile takip ederek, is tamamlaninca
     * attributes->url alanindan PDF adresini alabilirsiniz.
     */
    public function pdf(int|string $offerId): array
    {
        return $this->postAction($offerId, 'pdf');
    }

    /**
     * Teklif durumunu gunceller.
     *
     * @param  string  $status  'accepted' | 'rejected' | 'waiting'
     */
    public function updateStatus(int|string $offerId, string $status): array
    {
        $payload = JsonApiPayload::make($this->type(), ['status' => $status], [], $offerId);

        return $this->patchAction($offerId, 'update_status', $payload);
    }
}
