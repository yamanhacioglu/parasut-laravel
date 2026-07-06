<?php

namespace Northlab\Parasut\Resources;

/**
 * Calisan (Employee) yonetimi - masraf sahibi (spender) olarak da kullanilir.
 */
class EmployeeResource extends BaseResource
{
    protected string $endpoint = 'employees';

    protected ?string $jsonApiType = 'employees';

    public function archive(int|string $employeeId): array
    {
        return $this->patchAction($employeeId, 'archive');
    }

    public function unarchive(int|string $employeeId): array
    {
        return $this->patchAction($employeeId, 'unarchive');
    }
}
