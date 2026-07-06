<?php

namespace Northlab\Parasut\Auth;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CacheTokenRepository implements TokenRepositoryInterface
{
    public function __construct(
        protected CacheRepository $cache,
        protected string $key,
    ) {
    }

    public function get(): ?array
    {
        return $this->cache->get($this->key);
    }

    public function put(array $token): void
    {
        // Kalici bir sure ile saklariz; gercek gecerlilik "expires_at" alaninda
        // kontrol edildigi icin cache TTL'i uzun tutulur.
        $this->cache->put($this->key, $token, now()->addDays(30));
    }

    public function forget(): void
    {
        $this->cache->forget($this->key);
    }
}
