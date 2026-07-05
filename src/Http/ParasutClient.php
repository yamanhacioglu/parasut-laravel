<?php

namespace Northlab\Parasut\Http;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Northlab\Parasut\Auth\ParasutAuthenticator;
use Northlab\Parasut\Exceptions\ParasutAuthenticationException;
use Northlab\Parasut\Exceptions\ParasutException;
use Northlab\Parasut\Exceptions\ParasutNotFoundException;
use Northlab\Parasut\Exceptions\ParasutRateLimitException;
use Northlab\Parasut\Exceptions\ParasutServerException;
use Northlab\Parasut\Exceptions\ParasutValidationException;

class ParasutClient
{
    protected ?int $companyId;

    public function __construct(
        protected ParasutAuthenticator $authenticator,
        protected array $config,
        ?int $companyId = null,
    ) {
        $this->companyId = $companyId ?? (
            $config['default_company_id'] ? (int) $config['default_company_id'] : null
        );
    }

    /**
     * Belirli bir firma icin calisan yeni bir client ornegi dondurur.
     */
    public function withCompany(int $companyId): static
    {
        $clone = clone $this;
        $clone->companyId = $companyId;

        return $clone;
    }

    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    protected function ensureCompanyId(): int
    {
        if (! $this->companyId) {
            throw new ParasutException(
                'Firma (company_id) belirtilmedi. config/parasut.php icinde default_company_id ayarlayin '.
                'ya da ->forCompany($id) kullanin.'
            );
        }

        return $this->companyId;
    }

    protected function baseUrl(): string
    {
        return rtrim($this->config['base_url'], '/').'/'.trim($this->config['api_version'], '/');
    }

    /**
     * /me gibi firma bagimsiz genel uc noktalar icin tam URL uretir.
     */
    public function url(string $path): string
    {
        return $this->baseUrl().'/'.ltrim($path, '/');
    }

    /**
     * /{company_id}/... seklindeki firma bazli uc noktalar icin tam URL uretir.
     */
    public function companyUrl(string $path): string
    {
        return $this->baseUrl().'/'.$this->ensureCompanyId().'/'.ltrim($path, '/');
    }

    public function get(string $path, array $query = [], bool $companyScoped = true): array
    {
        return $this->request('GET', $path, ['query' => $query], $companyScoped);
    }

    public function post(string $path, array $payload = [], array $query = [], bool $companyScoped = true): array
    {
        return $this->request('POST', $path, ['json' => $payload, 'query' => $query], $companyScoped);
    }

    public function put(string $path, array $payload = [], array $query = [], bool $companyScoped = true): array
    {
        return $this->request('PUT', $path, ['json' => $payload, 'query' => $query], $companyScoped);
    }

    public function patch(string $path, array $payload = [], array $query = [], bool $companyScoped = true): array
    {
        return $this->request('PATCH', $path, ['json' => $payload, 'query' => $query], $companyScoped);
    }

    public function delete(string $path, array $payload = [], array $query = [], bool $companyScoped = true): array
    {
        return $this->request('DELETE', $path, ['json' => $payload, 'query' => $query], $companyScoped);
    }

    /**
     * PDF gibi binary/ham yanit bekleyen uc noktalar icin.
     */
    public function raw(string $method, string $path, array $options = [], bool $companyScoped = true): \Illuminate\Http\Client\Response
    {
        $this->throttle();

        $url = $companyScoped ? $this->companyUrl($path) : $this->url($path);

        $response = $this->pendingRequest()->send($method, $url, $options);

        if ($response->failed()) {
            $this->throwException($response);
        }

        return $response;
    }

    protected function request(string $method, string $path, array $options, bool $companyScoped): array
    {
        $this->throttle();

        $url = $companyScoped ? $this->companyUrl($path) : $this->url($path);

        $attempts = 0;
        $maxAttempts = max(1, (int) ($this->config['retry']['times'] ?? 3));

        while (true) {
            $attempts++;

            $request = $this->pendingRequest();

            $response = match (strtoupper($method)) {
                'GET' => $request->get($url, $options['query'] ?? []),
                'POST' => $request->withOptions(['query' => $options['query'] ?? []])->post($url, $options['json'] ?? []),
                'PUT' => $request->withOptions(['query' => $options['query'] ?? []])->put($url, $options['json'] ?? []),
                'PATCH' => $request->withOptions(['query' => $options['query'] ?? []])->patch($url, $options['json'] ?? []),
                'DELETE' => $request->withOptions(['query' => $options['query'] ?? []])->delete($url, $options['json'] ?? []),
                default => throw new ParasutException("Desteklenmeyen HTTP metodu: {$method}"),
            };

            $this->logRequest($method, $url, $options, $response->status());

            if ($response->status() === 429 && $attempts < $maxAttempts) {
                $retryAfter = (int) ($response->header('Retry-After') ?: 2);
                usleep(max($retryAfter, 1) * 1_000_000);

                continue;
            }

            if ($response->serverError() && $attempts < $maxAttempts) {
                usleep(((int) ($this->config['retry']['sleep_milliseconds'] ?? 500)) * 1000 * $attempts);

                continue;
            }

            break;
        }

        if ($response->failed()) {
            $this->throwException($response);
        }

        if ($response->status() === 204 || trim((string) $response->body()) === '') {
            return [];
        }

        return (array) $response->json();
    }

    protected function pendingRequest(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken($this->authenticator->getAccessToken())
            ->acceptJson()
            ->contentType('application/vnd.api+json')
            ->timeout((int) ($this->config['timeout'] ?? 30));
    }

    protected function throwException(\Illuminate\Http\Client\Response $response): void
    {
        $status = $response->status();
        $body = (array) $response->json();

        match (true) {
            $status === 401 => throw ParasutAuthenticationException::fromResponse($status, $body, 'Yetkilendirme basarisiz (401). Erisim tokeniniz gecersiz olabilir.'),
            $status === 404 => throw ParasutNotFoundException::fromResponse($status, $body, 'Istenen kayit bulunamadi (404).'),
            $status === 422 => throw ParasutValidationException::fromResponse($status, $body, 'Dogrulama hatasi (422).'),
            $status === 429 => throw (ParasutRateLimitException::fromResponse($status, $body, 'Istek limiti asildi (429).'))
                ->setRetryAfter((int) ($response->header('Retry-After') ?: null)),
            $status >= 500 => throw ParasutServerException::fromResponse($status, $body, 'Parasut sunucu hatasi.'),
            default => throw ParasutException::fromResponse($status, $body),
        };
    }

    /**
     * Parasut API 10 saniyede 10 istek limiti uygular. Bu metod, ayni surec
     * icinde ust uste yapilan cagrilarin bu limiti asmamasi icin basit bir
     * sliding-window kontrolu yapar (cache tabanli, coklu proses/worker
     * arasinda da paylasilir).
     */
    protected function throttle(): void
    {
        if (! ($this->config['rate_limit']['enabled'] ?? true)) {
            return;
        }

        $max = (int) ($this->config['rate_limit']['max_requests'] ?? 10);
        $window = (int) ($this->config['rate_limit']['per_seconds'] ?? 10);
        $key = 'northlab.parasut.rate_limit';

        $store = Cache::store($this->config['cache']['store'] ?? null);

        $count = $store->get($key, 0);

        if ($count >= $max) {
            usleep($window * 1_000_000);
            $store->forget($key);
            $count = 0;
        }

        if ($count === 0) {
            $store->put($key, 1, $window);
        } else {
            $store->increment($key);
        }
    }

    protected function logRequest(string $method, string $url, array $options, int $status): void
    {
        if (! ($this->config['log_requests'] ?? false)) {
            return;
        }

        Log::channel($this->config['log_channel'] ?? config('logging.default'))
            ->debug('[Parasut API]', [
                'method' => $method,
                'url' => $url,
                'query' => $options['query'] ?? [],
                'status' => $status,
            ]);
    }
}
