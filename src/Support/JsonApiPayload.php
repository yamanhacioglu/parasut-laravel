<?php

namespace Northlab\Parasut\Support;

/**
 * Parasut API, istekleri JSON:API benzeri bir formatta bekler:
 *
 *   {
 *     "data": {
 *       "type": "contacts",
 *       "attributes": { ... },
 *       "relationships": {
 *         "category": { "data": { "id": "1", "type": "item_categories" } }
 *       }
 *     }
 *   }
 *
 * Parasut'a ozgu bir detay: "details" gibi bazi iliskiler, sadece kaynak
 * kimligi degil, dogrudan "attributes" ve alt "relationships" de icerebilir
 * (fatura kalemi olusturma gibi bilesik/nested kayitlar icin). Bu helper her
 * iki kullanimi da destekler.
 */
class JsonApiPayload
{
    /**
     * @param  string  $type  JSON:API kaynak tipi (orn: "contacts", "sales_invoices")
     * @param  array  $attributes  Kaynagin attributes alani
     * @param  array  $relationships  ['iliski_adi' => resourceIdentifier|resourceIdentifier[], ...]
     * @param  string|int|null  $id  Guncelleme isteklerinde kaynak ID'si
     */
    public static function make(string $type, array $attributes = [], array $relationships = [], string|int|null $id = null): array
    {
        $data = ['type' => $type];

        if ($id !== null) {
            $data['id'] = (string) $id;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        if ($relationships !== []) {
            $data['relationships'] = self::formatRelationships($relationships);
        }

        return ['data' => $data];
    }

    /**
     * Ham "data" gövdesini dogrudan sarmalamak icin (ozel/karmasik payloadlar icin).
     */
    public static function wrap(array $data): array
    {
        return ['data' => $data];
    }

    protected static function formatRelationships(array $relationships): array
    {
        $result = [];

        foreach ($relationships as $key => $value) {
            // Zaten { "data": ... } formatinda geldiyse oldugu gibi kullan.
            if (is_array($value) && array_key_exists('data', $value) && count($value) === 1) {
                $result[$key] = $value;

                continue;
            }

            $result[$key] = ['data' => $value];
        }

        return $result;
    }

    /**
     * Tek bir kaynak referansi (to-one relationship) uretir.
     * Ornek: JsonApiPayload::ref('contacts', 123)
     */
    public static function ref(string $type, string|int $id): array
    {
        return ['id' => (string) $id, 'type' => $type];
    }

    /**
     * Coklu kaynak referanslari (to-many relationship) uretir.
     * Ornek: JsonApiPayload::refs('tags', [1, 2, 3])
     */
    public static function refs(string $type, array $ids): array
    {
        return array_map(fn ($id) => self::ref($type, $id), $ids);
    }

    /**
     * Nested (bilesik) bir kaynak olusturur - orn. fatura kalemi (detail).
     * attributes ve/veya kendi relationships'i olabilir (orn. product, warehouse).
     */
    public static function nested(string $type, array $attributes = [], array $relationships = [], string|int|null $id = null): array
    {
        $item = ['type' => $type];

        if ($id !== null) {
            $item['id'] = (string) $id;
        }

        if ($attributes !== []) {
            $item['attributes'] = $attributes;
        }

        if ($relationships !== []) {
            $item['relationships'] = self::formatRelationships($relationships);
        }

        return $item;
    }
}
