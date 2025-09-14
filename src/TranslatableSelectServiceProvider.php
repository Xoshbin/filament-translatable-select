<?php

namespace Xoshbin\TranslatableSelect;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Xoshbin\TranslatableSelect\Commands\TranslatableSelectCommand;
use Xoshbin\TranslatableSelect\Services\LocaleResolver;
use Xoshbin\TranslatableSelect\Services\TranslatableSearchService;
use Xoshbin\TranslatableSelect\Testing\TestsTranslatableSelect;

class TranslatableSelectServiceProvider extends PackageServiceProvider
{
    public static string $name = 'translatable-select';

    public static string $viewNamespace = 'translatable-select';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('xoshbin/translatable-select');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        // Register services
        $this->app->singleton(LocaleResolver::class);
        $this->app->singleton(TranslatableSearchService::class, function ($app) {
            return new TranslatableSearchService($app->make(LocaleResolver::class));
        });
    }

    public function packageBooted(): void
    {
        // Testing
        Testable::mixin(new TestsTranslatableSelect);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'xoshbin/translatable-select';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('translatable-select', __DIR__ . '/../resources/dist/components/translatable-select.js'),
            Css::make('translatable-select-styles', __DIR__ . '/../resources/dist/translatable-select.css'),
            Js::make('translatable-select-scripts', __DIR__ . '/../resources/dist/translatable-select.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            TranslatableSelectCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }
}
