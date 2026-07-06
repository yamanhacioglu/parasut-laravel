<?php

namespace Northlab\Parasut\Resources;

/**
 * Irsaliye (Shipment Document) yonetimi.
 */
class ShipmentDocumentResource extends BaseResource
{
    protected string $endpoint = 'shipment_documents';

    protected ?string $jsonApiType = 'shipment_documents';
}
