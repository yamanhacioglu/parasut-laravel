<?php

namespace Northlab\Parasut\Auth;

use Northlab\Parasut\Models\ParasutToken;

class DatabaseTokenRepository implements TokenRepositoryInterface
{
    public function __construct(
        protected string $connectionKey = 'default',
    ) {
    }

    public function get(): ?array
    {
        $token = ParasutToken::query()->where('connection_key', $this->connectionKey)->first();

        if (! $token) {
            return null;
        }

        return [
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'token_type' => $token->token_type,
            'expires_at' => $token->expires_at,
            'company_id' => $token->company_id,
        ];
    }

    public function put(array $token): void
    {
        ParasutToken::query()->updateOrCreate(
            ['connection_key' => $this->connectionKey],
            [
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_type' => $token['token_type'] ?? 'bearer',
                'expires_at' => $token['expires_at'] ?? null,
                'company_id' => $token['company_id'] ?? null,
            ]
        );
    }

    public function forget(): void
    {
        ParasutToken::query()->where('connection_key', $this->connectionKey)->delete();
    }
}
