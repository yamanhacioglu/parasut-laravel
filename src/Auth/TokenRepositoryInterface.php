<?php

namespace Northlab\Parasut\Auth;

interface TokenRepositoryInterface
{
    /**
     * Depolanan token verisini dondurur.
     * Donen dizi: ['access_token' => ..., 'refresh_token' => ..., 'expires_at' => Carbon|int|null, 'token_type' => ...]
     */
    public function get(): ?array;

    /**
     * Token verisini kalici olarak saklar.
     */
    public function put(array $token): void;

    /**
     * Saklanan token bilgisini siler.
     */
    public function forget(): void;
}
