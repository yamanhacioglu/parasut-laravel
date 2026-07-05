<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Http\ParasutClient;
use Northlab\Parasut\Support\JsonApiPayload;
use Northlab\Parasut\Support\QueryParameters;

abstract class BaseResource
{
    /**
     * Uc nokta path'i, orn: "contacts", "sales_invoices".
     */
    protected string $endpoint;

    /**
     * JSON:API "type" degeri. Belirtilmezse endpoint ile ayni kabul edilir.
     */
    protected ?string $jsonApiType = null;

    public function __construct(protected ParasutClient $client)
    {
    }

    public function type(): string
    {
        return $this->jsonApiType ?? $this->endpoint;
    }

    /**
     * Kayit listesini getirir.
     *
     * @param  array  $options  ['filter' => [...], 'sort' => '-created_at', 'page' => ['number'=>1,'size'=>25], 'include' => [...]]
     */
    public function list(array $options = []): array
    {
        return $this->client->get($this->endpoint, QueryParameters::fromArray($options));
    }

    /**
     * Tek bir kaydi ID ile getirir.
     */
    public function find(int|string $id, array $include = []): array
    {
        $query = $include !== [] ? ['include' => implode(',', $include)] : [];

        return $this->client->get("{$this->endpoint}/{$id}", $query);
    }

    /**
     * Yeni kayit olusturur.
     */
    public function create(array $attributes = [], array $relationships = [], array $include = []): array
    {
        $payload = JsonApiPayload::make($this->type(), $attributes, $relationships);
        $query = $include !== [] ? ['include' => implode(',', $include)] : [];

        return $this->client->post($this->endpoint, $payload, $query);
    }

    /**
     * Var olan kaydi gunceller.
     */
    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        $payload = JsonApiPayload::make($this->type(), $attributes, $relationships, $id);
        $query = $include !== [] ? ['include' => implode(',', $include)] : [];

        return $this->client->put("{$this->endpoint}/{$id}", $payload, $query);
    }

    /**
     * Kaydi siler.
     */
    public function delete(int|string $id): array
    {
        return $this->client->delete("{$this->endpoint}/{$id}");
    }

    protected function patchAction(int|string $id, string $action, array $payload = []): array
    {
        return $this->client->patch("{$this->endpoint}/{$id}/{$action}", $payload);
    }

    protected function postAction(int|string $id, string $action, array $payload = [], array $query = []): array
    {
        return $this->client->post("{$this->endpoint}/{$id}/{$action}", $payload, $query);
    }

    protected function deleteAction(int|string $id, string $action): array
    {
        return $this->client->delete("{$this->endpoint}/{$id}/{$action}");
    }

    protected function getAction(int|string $id, string $action, array $query = []): array
    {
        return $this->client->get("{$this->endpoint}/{$id}/{$action}", $query);
    }
}
