<?php

namespace Northlab\Parasut\Resources;

/**
 * Gelen e-Fatura kutusu (e-Invoice Inbox) - tedarikcilerden gelen
 * e-faturalarin listelenmesi icin (satin alma/ERP tarafi).
 */
class EInvoiceInboxResource extends BaseResource
{
    protected string $endpoint = 'e_invoice_inboxes';

    protected ?string $jsonApiType = 'e_invoice_inboxes';

    public function find(int|string $id, array $include = []): array
    {
        throw new \BadMethodCallException('e_invoice_inboxes icin show uc noktasi yoktur, sadece list() kullanilabilir.');
    }

    public function create(array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('e_invoice_inboxes salt okunurdur.');
    }

    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('e_invoice_inboxes salt okunurdur.');
    }

    public function delete(int|string $id): array
    {
        throw new \BadMethodCallException('e_invoice_inboxes salt okunurdur.');
    }
}
