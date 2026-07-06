<?php

namespace Northlab\Parasut\Console;

use Illuminate\Console\Command;
use Northlab\Parasut\Auth\ParasutAuthenticator;

class ParasutRefreshTokenCommand extends Command
{
    protected $signature = 'parasut:refresh-token';

    protected $description = 'Saklanan Parasut erisim tokenini yeniler ya da gerekirse yeniden olusturur';

    public function handle(ParasutAuthenticator $authenticator): int
    {
        try {
            $token = $authenticator->getAccessToken();
        } catch (\Throwable $e) {
            $this->error('Token yenileme basarisiz: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Gecerli access token: '.substr($token, 0, 25).'...');

        return self::SUCCESS;
    }
}
