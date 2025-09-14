<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Xoshbin\TranslatableSelect\Services\LocaleResolver;
use Xoshbin\TranslatableSelect\Services\TranslatableSearchService;

/**
 * Service provider for the Translatable Select package.
 */
class TranslatableSelectServiceProvider extends PackageServiceProvider
{
    public static string $name = 'translatable-select';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        // Register core services
        $this->app->singleton(LocaleResolver::class);
        $this->app->singleton(TranslatableSearchService::class, function ($app) {
            return new TranslatableSearchService($app->make(LocaleResolver::class));
        });
    }
}
