<?php

namespace Northlab\Parasut;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Northlab\Parasut\Auth\CacheTokenRepository;
use Northlab\Parasut\Auth\DatabaseTokenRepository;
use Northlab\Parasut\Auth\ParasutAuthenticator;
use Northlab\Parasut\Auth\TokenRepositoryInterface;
use Northlab\Parasut\Console\ParasutAuthorizeCommand;
use Northlab\Parasut\Console\ParasutRefreshTokenCommand;
use Northlab\Parasut\Http\ParasutClient;

class ParasutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/parasut.php', 'parasut');

        $this->app->singleton(TokenRepositoryInterface::class, function ($app) {
            $config = $app['config']->get('parasut');

            if (($config['token_storage'] ?? 'cache') === 'database') {
                return new DatabaseTokenRepository;
            }

            return new CacheTokenRepository(
                Cache::store($config['cache']['store'] ?? null),
                $config['cache']['key'] ?? 'northlab.parasut.token'
            );
        });

        $this->app->singleton(ParasutAuthenticator::class, function ($app) {
            return new ParasutAuthenticator(
                $app->make(TokenRepositoryInterface::class),
                $app['config']->get('parasut')
            );
        });

        $this->app->singleton(ParasutClient::class, function ($app) {
            return new ParasutClient(
                $app->make(ParasutAuthenticator::class),
                $app['config']->get('parasut')
            );
        });

        $this->app->singleton(ParasutManager::class, function ($app) {
            return new ParasutManager(
                $app->make(ParasutAuthenticator::class),
                $app->make(ParasutClient::class)
            );
        });

        $this->app->alias(ParasutManager::class, 'parasut');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/parasut.php' => config_path('parasut.php'),
            ], 'parasut-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'parasut-migrations');

            $this->commands([
                ParasutAuthorizeCommand::class,
                ParasutRefreshTokenCommand::class,
            ]);
        }
    }

    public function provides(): array
    {
        return [
            TokenRepositoryInterface::class,
            ParasutAuthenticator::class,
            ParasutClient::class,
            ParasutManager::class,
            'parasut',
        ];
    }
}
