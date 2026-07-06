<?php

namespace Northlab\Parasut\Resources;

use Northlab\Parasut\Support\JsonApiPayload;

/**
 * Musteri / Tedarikci (Contact) islemleri.
 *
 * @see https://apidocs.parasut.com/ Contacts
 */
class ContactResource extends BaseResource
{
    protected string $endpoint = 'contacts';

    protected ?string $jsonApiType = 'contacts';

    /**
     * Bir musteriye/tedarikciye borc (debit) veya alacak (credit) kaydi ekler.
     * Bu, faturasiz cari hareket girisi icin kullanilir (acilis bakiyesi, mahsup vb).
     */
    public function creditTransaction(int|string $contactId, array $attributes): array
    {
        $payload = JsonApiPayload::make('contact_credit_transactions', $attributes);

        return $this->postAction($contactId, 'contact_credit_transactions', $payload);
    }

    public function debitTransaction(int|string $contactId, array $attributes): array
    {
        $payload = JsonApiPayload::make('contact_debit_transactions', $attributes);

        return $this->postAction($contactId, 'contact_debit_transactions', $payload);
    }
}
