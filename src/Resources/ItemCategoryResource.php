<?php

namespace Northlab\Parasut\Resources;

/**
 * Urun/Fatura kategorileri (Item Categories).
 */
class ItemCategoryResource extends BaseResource
{
    protected string $endpoint = 'item_categories';

    protected ?string $jsonApiType = 'item_categories';
}
