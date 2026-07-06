<?php

namespace Northlab\Parasut\Resources;

/**
 * Urun / Stok karti (Product) islemleri. E-ticaret entegrasyonlarinda
 * urun senkronizasyonu icin ana kaynak.
 */
class ProductResource extends BaseResource
{
    protected string $endpoint = 'products';

    protected ?string $jsonApiType = 'products';

    /**
     * Bir urunun depo bazli stok seviyelerini getirir.
     */
    public function inventoryLevels(int|string $productId): array
    {
        return $this->client->get("product/{$productId}/inventory_levels");
    }
}
