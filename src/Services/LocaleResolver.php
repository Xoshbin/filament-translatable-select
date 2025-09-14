<?php

namespace Xoshbin\TranslatableSelect\Services;

use Filament\Facades\Filament;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;

/**
 * Service for resolving available locales dynamically from various sources.
 *
 * This service provides a centralized way to determine which locales are available
 * for translatable search functionality, with fallback mechanisms to ensure
 * the system works in various configurations.
 */
class LocaleResolver
{
    /**
     * Cache for resolved locales to avoid repeated lookups.
     */
    private ?array $cachedLocales = null;

    /**
     * Get the available locales for translation search.
     *
     * Priority order:
     * 1. Filament Spatie Translatable plugin configuration
     * 2. Application locale configuration
     * 3. Default fallback to English
     */
    public function getAvailableLocales(): array
    {
        if ($this->cachedLocales !== null) {
            return $this->cachedLocales;
        }

        $this->cachedLocales = $this->resolveLocales();

        return $this->cachedLocales;
    }

    /**
     * Get the current application locale.
     */
    public function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * Check if a locale is supported.
     */
    public function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, $this->getAvailableLocales(), true);
    }

    /**
     * Clear the locale cache (useful for testing or dynamic configuration changes).
     */
    public function clearCache(): void
    {
        $this->cachedLocales = null;
    }

    /**
     * Resolve locales from various sources with fallback mechanism.
     */
    private function resolveLocales(): array
    {
        $strategy = config('translatable-select.locale_strategy', 'auto');

        switch ($strategy) {
            case 'manual':
                return config('translatable-select.manual_locales', ['en']);

            case 'filament':
                $locales = $this->getLocalesFromFilamentPlugin();

                return ! empty($locales) ? $locales : ['en'];

            case 'config':
                $locales = $this->getLocalesFromAppConfig();

                return ! empty($locales) ? $locales : ['en'];

            case 'auto':
            default:
                // Try to get locales from Filament Spatie Translatable plugin
                $locales = $this->getLocalesFromFilamentPlugin();

                if (! empty($locales)) {
                    return $locales;
                }

                // Fallback to application configuration
                $locales = $this->getLocalesFromAppConfig();

                if (! empty($locales)) {
                    return $locales;
                }

                // Final fallback to default
                return ['en'];
        }
    }

    /**
     * Get locales from Filament Spatie Translatable plugin.
     */
    private function getLocalesFromFilamentPlugin(): array
    {
        try {
            // Get the current panel
            $panel = Filament::getCurrentPanel();

            if (! $panel) {
                return [];
            }

            // Find the SpatieTranslatablePlugin
            $plugins = $panel->getPlugins();

            foreach ($plugins as $plugin) {
                if ($plugin instanceof SpatieTranslatablePlugin) {
                    $locales = $plugin->getDefaultLocales();

                    if (is_array($locales) && ! empty($locales)) {
                        return $locales;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail and try next source
        }

        return [];
    }

    /**
     * Get locales from application configuration.
     */
    private function getLocalesFromAppConfig(): array
    {
        // Try configured keys from translatable-select config
        $configKeys = config('translatable-select.config_keys', [
            'app.supported_locales',
            'app.locales',
            'translatable.locales',
            'filament-spatie-translatable.default_locales',
        ]);

        foreach ($configKeys as $key) {
            $locales = config($key);

            if (is_array($locales) && ! empty($locales)) {
                return $locales;
            }
        }

        // If no array configuration found, build from app.locale
        $appLocale = config('app.locale', 'en');
        $fallbackLocale = config('app.fallback_locale', 'en');

        $locales = [$appLocale];

        if ($fallbackLocale !== $appLocale) {
            $locales[] = $fallbackLocale;
        }

        return array_unique($locales);
    }
}
