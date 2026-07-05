<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Parasut API Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('PARASUT_BASE_URL', 'https://api.parasut.com'),

    'api_version' => env('PARASUT_API_VERSION', 'v4'),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Kimlik Bilgileri
    |--------------------------------------------------------------------------
    | Parasut destek@parasut.com adresinden CLIENT_ID / CLIENT_SECRET alinir.
    */
    'client_id' => env('PARASUT_CLIENT_ID'),
    'client_secret' => env('PARASUT_CLIENT_SECRET'),
    'redirect_uri' => env('PARASUT_REDIRECT_URI', 'urn:ietf:wg:oauth:2.0:oob'),

    /*
    |--------------------------------------------------------------------------
    | Grant Type
    |--------------------------------------------------------------------------
    | "password"           : sunucu-sunucu entegrasyonlarda (email/sifre ile)
    | "authorization_code" : kullanici bazli yetkilendirme (redirect flow)
    | "refresh_token"       : mevcut refresh_token ile yenileme (otomatik kullanilir)
    */
    'grant_type' => env('PARASUT_GRANT_TYPE', 'password'),

    // grant_type=password icin gerekli
    'username' => env('PARASUT_USERNAME'),
    'password' => env('PARASUT_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Firma (Company) Numarasi
    |--------------------------------------------------------------------------
    | Tum istekler /v4/{company_id}/... seklinde atilir. Buraya varsayilan
    | firma numaranizi yazabilir, ya da her cagride ->forCompany($id) ile
    | override edebilirsiniz.
    */
    'default_company_id' => env('PARASUT_COMPANY_ID'),

    /*
    |--------------------------------------------------------------------------
    | Token Depolama
    |--------------------------------------------------------------------------
    | "cache"    : Laravel cache uzerinde saklanir (varsayilan, kurulum gerektirmez)
    | "database" : parasut_tokens tablosunda saklanir (migration yayinlanmali)
    */
    'token_storage' => env('PARASUT_TOKEN_STORAGE', 'cache'),

    'cache' => [
        'store' => env('PARASUT_CACHE_STORE', null), // null = default cache store
        'key' => env('PARASUT_CACHE_KEY', 'northlab.parasut.token'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Parasut API 10 saniyede 10 istek limiti uygular. Paket, bu limiti asmamak
    | icin istekleri kendi tarafinda kuyruklar/bekletir.
    */
    'rate_limit' => [
        'enabled' => env('PARASUT_RATE_LIMIT_ENABLED', true),
        'max_requests' => env('PARASUT_RATE_LIMIT_MAX', 10),
        'per_seconds' => env('PARASUT_RATE_LIMIT_SECONDS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Ayarlari
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'times' => env('PARASUT_RETRY_TIMES', 3),
        'sleep_milliseconds' => env('PARASUT_RETRY_SLEEP_MS', 500),
    ],

    'timeout' => env('PARASUT_HTTP_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Loglama
    |--------------------------------------------------------------------------
    | true ise tum istek/yanitlar Laravel log kanalina yazilir (debug amacli).
    */
    'log_requests' => env('PARASUT_LOG_REQUESTS', false),
    'log_channel' => env('PARASUT_LOG_CHANNEL', null), // null = default channel

];
