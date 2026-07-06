<?php

namespace Northlab\Parasut\Auth;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Northlab\Parasut\Exceptions\ParasutAuthenticationException;

class ParasutAuthenticator
{
    protected array $config;

    public function __construct(
        protected TokenRepositoryInterface $repository,
        array $config,
    ) {
        $this->config = $config;
    }

    protected function oauthBaseUrl(): string
    {
        return rtrim($this->config['base_url'], '/');
    }

    /**
     * Gecerli bir access_token dondurur. Gerekirse yeniler ya da yeniden
     * kimlik dogrulamasi yapar.
     */
    public function getAccessToken(): string
    {
        $token = $this->repository->get();

        if ($token && ! $this->isExpired($token)) {
            return $token['access_token'];
        }

        if ($token && ! empty($token['refresh_token'])) {
            try {
                $token = $this->refresh($token['refresh_token']);

                return $token['access_token'];
            } catch (ParasutAuthenticationException) {
                // refresh basarisiz olduysa yeniden ilk kimlik dogrulamayi dene
            }
        }

        $token = $this->authenticate();

        return $token['access_token'];
    }

    /**
     * Konfigurasyondaki grant_type'a gore ilk kimlik dogrulamasini yapar.
     */
    public function authenticate(?string $authorizationCode = null): array
    {
        $grantType = $authorizationCode ? 'authorization_code' : $this->config['grant_type'];

        $payload = match ($grantType) {
            'password' => [
                'grant_type' => 'password',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'username' => $this->config['username'],
                'password' => $this->config['password'],
                'redirect_uri' => $this->config['redirect_uri'],
            ],
            'authorization_code' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'code' => $authorizationCode,
                'redirect_uri' => $this->config['redirect_uri'],
            ],
            default => throw new ParasutAuthenticationException(
                "Desteklenmeyen grant_type: {$grantType}"
            ),
        };

        return $this->requestToken($payload);
    }

    /**
     * Refresh token kullanarak yeni bir access token alir.
     */
    public function refresh(string $refreshToken): array
    {
        return $this->requestToken([
            'grant_type' => 'refresh_token',
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'refresh_token' => $refreshToken,
            'redirect_uri' => $this->config['redirect_uri'],
        ]);
    }

    /**
     * grant_type=authorization_code akisi icin kullaniciyi yonlendirecegimiz
     * yetkilendirme URL'ini uretir.
     */
    public function authorizationUrl(): string
    {
        $query = http_build_query([
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
        ]);

        return "{$this->oauthBaseUrl()}/oauth/authorize?{$query}";
    }

    protected function requestToken(array $payload): array
    {
        $response = Http::asForm()
            ->timeout($this->config['timeout'] ?? 30)
            ->post("{$this->oauthBaseUrl()}/oauth/token", $payload);

        if ($response->failed()) {
            $body = (array) $response->json();

            throw ParasutAuthenticationException::fromResponse(
                $response->status(),
                $body,
                $this->describeOAuthError($body, $response->status())
            );
        }

        $data = $response->json();

        $token = [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'token_type' => $data['token_type'] ?? 'bearer',
            'expires_at' => Carbon::now()->addSeconds(($data['expires_in'] ?? 7200) - 60),
        ];

        $this->repository->put($token);

        return $token;
    }

    /**
     * Parasut'un /oauth/token uc noktasi standart JSON:API "errors" formati
     * yerine OAuth2 standardi olan {"error": "...", "error_description": "..."}
     * formatinda hata doner. Bu metod, gercek nedeni (gecersiz client_id,
     * yanlis kullanici/sifre, redirect_uri uyusmazligi vb.) kullaniciya
     * gosterecek sekilde ayiklar.
     */
    protected function describeOAuthError(array $body, int $status): string
    {
        $error = $body['error'] ?? null;
        $description = $body['error_description'] ?? null;

        if (! $error && ! $description) {
            return "Parasut OAuth2 kimlik dogrulamasi basarisiz oldu (HTTP {$status}). Yanit: ".json_encode($body);
        }

        $hint = match ($error) {
            'invalid_client' => ' -> PARASUT_CLIENT_ID / PARASUT_CLIENT_SECRET degerlerini kontrol edin.',
            'invalid_grant' => ' -> PARASUT_USERNAME / PARASUT_PASSWORD hatali olabilir ya da hesabin iki adimli dogrulamasi (2FA) acik olabilir (bu durumda password grant calismaz, authorization_code kullanmaniz gerekir).',
            'invalid_request' => ' -> PARASUT_REDIRECT_URI degerinin Parasut uygulama ayarlarindaki ile birebir ayni oldugundan emin olun.',
            'unauthorized_client' => ' -> Bu client_id icin secilen grant_type (password) yetkili degil. Parasut destek ekibinden client uygulamanizin "password" grant icin yetkilendirildigini teyit edin.',
            default => '',
        };

        return "Parasut OAuth2 hatasi: [{$error}] {$description}{$hint}";
    }

    protected function isExpired(array $token): bool
    {
        $expiresAt = $token['expires_at'] ?? null;

        if (! $expiresAt) {
            return true;
        }

        $expiresAt = $expiresAt instanceof Carbon ? $expiresAt : Carbon::parse($expiresAt);

        return Carbon::now()->greaterThanOrEqualTo($expiresAt);
    }

    /**
     * Depolanan tokeni temizler; bir sonraki istekte yeniden kimlik
     * dogrulama yapilmasini zorunlu kilar.
     */
    public function forget(): void
    {
        $this->repository->forget();
    }
}
