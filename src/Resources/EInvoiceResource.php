<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * e-Fatura islemleri. Ayni EArchiveResource gibi, olusturma islemi
 * asenkron calisir (trackable_job donen). Detayli aciklama icin
 * EArchiveResource dokblogu ile ayni akisi izleyin.
 */
class EInvoiceResource extends BaseResource
{
    protected string $endpoint = 'e_invoices';

    protected ?string $jsonApiType = 'e_invoices';

    public function createFromSalesInvoice(int|string $salesInvoiceId, array $attributes = []): array
    {
        $relationships = ['sales_invoice' => JsonApiPayload::ref('sales_invoices', $salesInvoiceId)];

        return $this->create($attributes, $relationships);
    }

    public function pdf(int|string $id): array
    {
        return $this->getAction($id, 'pdf');
    }

    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('e_invoices guncellenemez.');
    }

    public function delete(int|string $id): array
    {
        throw new \BadMethodCallException('e_invoices silinemez.');
    }

    public function list(array $options = []): array
    {
        throw new \BadMethodCallException('e_invoices icin list uc noktasi yoktur; gelen e-faturalar icin EInvoiceInboxResource kullanin.');
    }
}
