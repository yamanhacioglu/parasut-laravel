<?php

namespace Northlab\Parasut\Resources;

/**
 * Finansal hareket (Transaction) - odeme/tahsilat kayitlarinin dusuk
 * seviyeli temsili. Sadece goruntuleme ve silme destekler.
 */
class TransactionResource extends BaseResource
{
    protected string $endpoint = 'transactions';

    protected ?string $jsonApiType = 'transactions';

    public function list(array $options = []): array
    {
        throw new \BadMethodCallException('transactions icin list uc noktasi yoktur; ID ile find() kullanin ya da AccountResource::transactions() ile hesap bazli listeleyin.');
    }

    public function create(array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('transactions dogrudan olusturulamaz; faturalardaki payments() metodlarini kullanin.');
    }

    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('transactions guncellenemez.');
    }
}
