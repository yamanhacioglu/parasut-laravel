<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * Toplu/tekil stok guncelleme. E-ticaret <-> ERP stok senkronizasyonunun
 * kalbidir: siparis olustugunda stok dusmek, iade oldugunda stok geri
 * eklemek gibi islemler icin kullanilir.
 *
 * Not: Bu uc nokta sadece POST (create) destekler; guncelleme/liste/silme
 * yoktur. Sonuc bazen "trackable_jobs" uzerinden asenkron donebilir; bu
 * yuzden yaniti TrackableJobResource ile takip etmeniz gerekebilir.
 */
class StockUpdateResource extends BaseResource
{
    protected string $endpoint = 'stock_updates';

    protected ?string $jsonApiType = 'stock_updates';

    /**
     * @param  array  $details  Her biri: ['product_id' => int, 'warehouse_id' => int|null,
     *                          'quantity' => float, 'movement_direction' => 'in'|'out', ...]
     *                          (StockUpdateDetailAttributes alanlarina bakiniz)
     */
    public function create(array $attributes = [], array $relationships = [], array $include = []): array
    {
        $detailItems = array_map(function (array $detail) {
            $product = $detail['product_id'] ?? null;
            $warehouse = $detail['warehouse_id'] ?? null;
            unset($detail['product_id'], $detail['warehouse_id']);

            $detailRelationships = [];

            if ($product) {
                $detailRelationships['product'] = JsonApiPayload::ref('products', $product);
            }

            if ($warehouse) {
                $detailRelationships['warehouse'] = JsonApiPayload::ref('warehouses', $warehouse);
            }

            return JsonApiPayload::nested('stock_update_details', $detail, $detailRelationships);
        }, $relationships['details'] ?? []);

        unset($relationships['details']);

        if ($detailItems !== []) {
            $relationships['details'] = $detailItems;
        }

        return parent::create($attributes, $relationships, $include);
    }

    /**
     * Kolay kullanim: tek bir urunun stogunu belirli bir depoda gunceller.
     *
     * @param  string  $direction  'in' (stok girisi) ya da 'out' (stok cikisi)
     */
    public function adjust(int|string $productId, float $quantity, string $direction = 'out', ?int $warehouseId = null, array $extra = []): array
    {
        return $this->create([], [
            'details' => [
                array_merge([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $quantity,
                    'movement_direction' => $direction,
                ], $extra),
            ],
        ]);
    }

    public function find(int|string $id, array $include = []): array
    {
        throw new \BadMethodCallException('stock_updates icin show/list/update/delete uc noktasi yoktur.');
    }

    public function list(array $options = []): array
    {
        throw new \BadMethodCallException('stock_updates icin list uc noktasi yoktur. Gecmis hareketler icin StockMovementResource kullanin.');
    }

    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('stock_updates guncellenemez, her hareket icin yeni bir kayit olusturun.');
    }

    public function delete(int|string $id): array
    {
        throw new \BadMethodCallException('stock_updates silinemez.');
    }
}
