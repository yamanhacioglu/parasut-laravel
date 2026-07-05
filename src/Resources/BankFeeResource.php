<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * Banka masrafi (Bank Fee) kayitlari. Not: Parasut API bu kaynak icin
 * listeleme (index) uc noktasi sunmaz; sadece olusturma/goruntuleme/
 * guncelleme/silme mumkundur.
 */
class BankFeeResource extends BaseResource
{
    protected string $endpoint = 'bank_fees';

    protected ?string $jsonApiType = 'bank_fees';

    public function list(array $options = []): array
    {
        throw new \BadMethodCallException('bank_fees icin list (index) uc noktasi Parasut API tarafinda mevcut degildir.');
    }

    public function archive(int|string $id): array
    {
        return $this->patchAction($id, 'archive');
    }

    public function unarchive(int|string $id): array
    {
        return $this->patchAction($id, 'unarchive');
    }

    public function pay(int|string $id, array $attributes): array
    {
        $payload = JsonApiPayload::make('payments', $attributes);

        return $this->postAction($id, 'payments', $payload);
    }
}
