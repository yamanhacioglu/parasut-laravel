<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;
use Northlab\Parasut\Support\QueryParameters;

/**
 * Kasa / Banka hesabi (Account) yonetimi.
 */
class AccountResource extends BaseResource
{
    protected string $endpoint = 'accounts';

    protected ?string $jsonApiType = 'accounts';

    /**
     * Hesaba manuel alacak (para girisi) kaydi ekler.
     */
    public function creditTransaction(int|string $accountId, array $attributes): array
    {
        $payload = JsonApiPayload::make('account_credit_transactions', $attributes);

        return $this->postAction($accountId, 'credit_transactions', $payload);
    }

    /**
     * Hesaptan manuel borc (para cikisi) kaydi ekler.
     */
    public function debitTransaction(int|string $accountId, array $attributes): array
    {
        $payload = JsonApiPayload::make('account_debit_transactions', $attributes);

        return $this->postAction($accountId, 'debit_transactions', $payload);
    }

    /**
     * Hesabin hareket (transaction) listesini getirir.
     */
    public function transactions(int|string $accountId, array $options = []): array
    {
        return $this->getAction($accountId, 'transactions', QueryParameters::fromArray($options));
    }
}
