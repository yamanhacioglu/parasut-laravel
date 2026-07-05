<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * Vergi odemesi (Tax) kayitlari.
 *
 * Not: Parasut API dokumantasyonunda bu kaynagin JSON:API "type" degeri
 * "bank_fees" olarak tanimlanmistir (Taxes, BankFees ile ayni alt yapiyi
 * paylasir); paket bu detayi sizin icin otomatik yonetir.
 */
class TaxResource extends BaseResource
{
    protected string $endpoint = 'taxes';

    protected ?string $jsonApiType = 'bank_fees';

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
