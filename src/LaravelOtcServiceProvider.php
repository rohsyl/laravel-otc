<?php

namespace rohsyl\LaravelOtc;

use rohsyl\LaravelOtc\Generators\GeneratorContract;
use rohsyl\LaravelOtc\Generators\NumberGenerator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

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
}