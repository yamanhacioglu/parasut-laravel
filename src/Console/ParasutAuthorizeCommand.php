<?php

namespace Northlab\Parasut\Console;

use Illuminate\Console\Command;
use Northlab\Parasut\Auth\ParasutAuthenticator;

class ParasutAuthorizeCommand extends Command
{
    protected $signature = 'parasut:authorize {--code= : authorization_code grant icin donen "code" degeri}';

    protected $description = 'Parasut API icin ilk OAuth2 kimlik dogrulamasini yapar ve tokeni saklar';

    public function handle(ParasutAuthenticator $authenticator): int
    {
        $grantType = config('parasut.grant_type');
        $code = $this->option('code');

        if ($grantType === 'authorization_code' && ! $code) {
            $this->info('Once asagidaki adrese giderek uygulamayi yetkilendirin:');
            $this->line($authenticator->authorizationUrl());
            $this->newLine();
            $this->info('Ardindan donen "code" degeri ile tekrar calistirin:');
            $this->line('  php artisan parasut:authorize --code=XXXXX');

            return self::SUCCESS;
        }

        try {
            $token = $authenticator->authenticate($code);
        } catch (\Throwable $e) {
            $this->error('Kimlik dogrulama basarisiz: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Parasut kimlik dogrulamasi basarili. Token saklandi.');
        $this->table(
            ['access_token', 'refresh_token', 'expires_at'],
            [[
                substr($token['access_token'], 0, 20).'...',
                $token['refresh_token'] ? substr($token['refresh_token'], 0, 20).'...' : '-',
                (string) $token['expires_at'],
            ]]
        );

        return self::SUCCESS;
    }
}
