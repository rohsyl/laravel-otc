<?php

namespace rohsyl\LaravelOtc;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use rohsyl\LaravelOtc\Generators\GeneratorContract;
use rohsyl\LaravelOtc\Generators\NumberGenerator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use rohsyl\LaravelOtc\Http\Middlewares\OtcMiddleware;

class LaravelOtcServiceProvider extends PackageServiceProvider
{

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('otc')
            ->hasConfigFile('otc')
            //->hasViews()
            ->hasRoute('web')
            ->hasMigrations([
                'create_otc_tokens_table'
            ])
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('rohsyl/laravel-otc');
            });
    }

    public function packageRegistered()
    {
        $this->app->bind(GeneratorContract::class, NumberGenerator::class);
        $this->app->singleton(LaravelOtcManager::class, function($app) {
            return new LaravelOtcManager($app->make(GeneratorContract::class));
        });
    }

    public function packageBooted()
    {
        RateLimiter::for('laravel-otc', function($request) {
            return Limit::perMinute(config('otc.rate-limit.per-minute', 6));
        });

        $this->app['router']->aliasMiddleware('otc', OtcMiddleware::class);
    }
}
