<?php

namespace Northlab\Parasut\Resources;

/**
 * Stok hareketleri (salt okunur listeleme). Urun giris/cikis gecmisini
 * takip etmek icin kullanilir.
 */
class StockMovementResource extends BaseResource
{
    protected string $endpoint = 'stock_movements';

    protected ?string $jsonApiType = 'stock_movements';

    public function find(int|string $id, array $include = []): array
    {
        throw new \BadMethodCallException('Parasut API stock_movements icin tekil kayit (show) uc noktasi sunmaz, sadece list() kullanilabilir.');
    }

    public function create(array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('stock_movements salt okunurdur. Stok guncellemek icin StockUpdateResource kullanin.');
    }

    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('stock_movements salt okunurdur. Stok guncellemek icin StockUpdateResource kullanin.');
    }

    public function delete(int|string $id): array
    {
        throw new \BadMethodCallException('stock_movements salt okunurdur.');
    }
}
