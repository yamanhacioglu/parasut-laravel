<?php

namespace Northlab\Parasut\Resources;

/**
 * Etiket (Tag) yonetimi - fatura, musteri, urun gibi kayitlari
 * etiketlemek icin kullanilir.
 */
class TagResource extends BaseResource
{
    protected string $endpoint = 'tags';

    protected ?string $jsonApiType = 'tags';
}
