<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * e-Arsiv fatura islemleri.
 *
 * ONEMLI: create() cagrisi ANLIK olarak e-Arsiv olusturmaz; Parasut bunu
 * asenkron bir "trackable_job" olarak isler. Akis:
 *   1. create() -> donen data.id bir trackable_job ID'sidir
 *   2. TrackableJobResource::find($jobId) ile is durumunu "succeeded" olana
 *      kadar polling yapin (kucuk araliklarla tekrar sorgulayin)
 *   3. Is tamamlaninca job kaydinin relationships/attributes alaninda
 *      gercek e_archive ID'sini bulup find()/pdf() ile devam edin
 */
class EArchiveResource extends BaseResource
{
    protected string $endpoint = 'e_archives';

    protected ?string $jsonApiType = 'e_archives';

    /**
     * Var olan bir satis faturasindan e-Arsiv olusturur.
     */
    public function createFromSalesInvoice(int|string $salesInvoiceId, array $attributes = []): array
    {
        $relationships = ['sales_invoice' => JsonApiPayload::ref('sales_invoices', $salesInvoiceId)];

        return $this->create($attributes, $relationships);
    }

    /**
     * e-Arsiv PDF adresini (link) dondurur.
     */
    public function pdf(int|string $id): array
    {
        return $this->getAction($id, 'pdf');
    }

    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('e_archives guncellenemez.');
    }

    public function delete(int|string $id): array
    {
        throw new \BadMethodCallException('e_archives silinemez.');
    }

    public function list(array $options = []): array
    {
        throw new \BadMethodCallException('e_archives icin list uc noktasi yoktur; ilgili sales_invoice uzerinden include=active_e_document kullanin.');
    }
}
