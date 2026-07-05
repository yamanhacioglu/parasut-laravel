<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * e-SMM (e-Serbest Meslek Makbuzu) islemleri. Olusturma asenkron
 * calisir (trackable_job), EArchiveResource ile ayni akisi izler.
 */
class ESmmResource extends BaseResource
{
    protected string $endpoint = 'e_smms';

    protected ?string $jsonApiType = 'e_smms';

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
        throw new \BadMethodCallException('e_smms guncellenemez.');
    }

    public function delete(int|string $id): array
    {
        throw new \BadMethodCallException('e_smms silinemez.');
    }

    public function list(array $options = []): array
    {
        throw new \BadMethodCallException('e_smms icin list uc noktasi yoktur.');
    }
}
