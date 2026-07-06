<?php

namespace Northlab\Parasut\Resources;

/**
 * Oturum sahibi kullanici bilgisi (/me). Firma bagimsizdir; hangi
 * firmalara (company_id) erisiminiz oldugunu bulmak icin kullanislidir.
 */
class MeResource extends BaseResource
{
    protected string $endpoint = 'me';

    protected ?string $jsonApiType = 'users';

    public function get(array $include = []): array
    {
        $query = $include !== [] ? ['include' => implode(',', $include)] : [];

        return $this->client->get('me', $query, companyScoped: false);
    }

    public function list(array $options = []): array
    {
        throw new \BadMethodCallException('me icin get() kullanin.');
    }

    public function find(int|string $id, array $include = []): array
    {
        throw new \BadMethodCallException('me icin get() kullanin.');
    }

    public function create(array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('me olusturulamaz.');
    }

    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('me guncellenemez.');
    }

    public function delete(int|string $id): array
    {
        throw new \BadMethodCallException('me silinemez.');
    }
}
