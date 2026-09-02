<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\DevCommands;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureDevServerForLan();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Force `php artisan serve` à écouter sur 0.0.0.0 pour le réseau local.
     * Priorité USERLAND > DEFAULT donc écrase le `serve` par défaut de DevCommands.
     */
    protected function configureDevServerForLan(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $host = env('SERVER_HOST', '0.0.0.0');
        $port = env('SERVER_PORT', '8000');

        DevCommands::artisan("serve --host={$host} --port={$port}", 'server');
    }
}
