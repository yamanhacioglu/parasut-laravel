<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * Satis teklifi / fatura gibi belgeleri e-posta ile paylasma (sharing).
 */
class SharingResource extends BaseResource
{
    protected string $endpoint = 'sharings';

    protected ?string $jsonApiType = 'sharing_forms';

    /**
     * Bir belgeyi (varsayilan: sales_offers) e-posta ile paylasir.
     *
     * @param  array  $email  ['addresses' => 'a@b.com,c@d.com', 'subject' => '...', 'body' => '...']
     * @param  array  $portal  ['has_online_collection'=>bool, 'has_online_payment_reminder'=>bool, 'has_referral_link'=>bool]
     */
    public function share(int|string $shareableId, array $email, string $shareableType = 'sales_offers', array $portal = []): array
    {
        $attributes = ['email' => $email];

        if ($portal !== []) {
            $attributes['portal'] = $portal;
        }

        $relationships = [
            'shareable' => JsonApiPayload::ref($shareableType, $shareableId),
        ];

        return $this->create($attributes, $relationships);
    }

    public function list(array $options = []): array
    {
        throw new \BadMethodCallException('sharings icin list uc noktasi yoktur.');
    }

    public function find(int|string $id, array $include = []): array
    {
        throw new \BadMethodCallException('sharings icin show uc noktasi yoktur.');
    }

    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('sharings guncellenemez.');
    }

    public function delete(int|string $id): array
    {
        throw new \BadMethodCallException('sharings silinemez.');
    }
}
