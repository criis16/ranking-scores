<?php

namespace App\Providers;

use App\User\Domain\UserRepository;
use App\User\Infrastructure\Persistence\PgsqlUserRepository;
use Illuminate\Support\ServiceProvider;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PDO::class, fn() => new PDO(
            sprintf('pgsql:host=%s;port=%s;dbname=%s', config('database.connections.pgsql.host'),
                config('database.connections.pgsql.port'), config('database.connections.pgsql.database')),
            config('database.connections.pgsql.username'),
            config('database.connections.pgsql.password'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        ));
        $this->app->bind(UserRepository::class, PgsqlUserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
