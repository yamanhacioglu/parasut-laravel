<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * Maas (Salary) kayitlari.
 */
class SalaryResource extends BaseResource
{
    protected string $endpoint = 'salaries';

    protected ?string $jsonApiType = 'salaries';

    public function archive(int|string $id): array
    {
        return $this->patchAction($id, 'archive');
    }

    public function unarchive(int|string $id): array
    {
        return $this->patchAction($id, 'unarchive');
    }

    public function pay(int|string $id, array $attributes): array
    {
        $payload = JsonApiPayload::make('payments', $attributes);

        return $this->postAction($id, 'payments', $payload);
    }
}
