<?php

namespace Northlab\Parasut\Resources;

/**
 * Depo (Warehouse) yonetimi. Coklu depo/stok noktasi olan e-ticaret
 * entegrasyonlarinda kullanilir.
 */
class WarehouseResource extends BaseResource
{
    protected string $endpoint = 'warehouses';

    protected ?string $jsonApiType = 'warehouses';
}
