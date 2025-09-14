<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Spatie\Translatable\HasTranslations;

/**
 * Service for resolving locales for translatable models and application.
 */
class LocaleResolver
{
    /**
     * Get the current application locale.
     */
    public function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Get the fallback locale for the application.
     */
    public function getFallbackLocale(): string
    {
        return Config::get('app.fallback_locale', 'en');
    }

    /**
     * Get all available locales for the application.
     */
    public function getAvailableLocales(): array
    {
        // Try to get from config first
        $locales = Config::get('translatable-select.available_locales');

        if (! empty($locales)) {
            return $locales;
        }

        // Fallback to common configuration locations
        $locales = Config::get('translatable.locales')
            ?? Config::get('app.available_locales')
            ?? [$this->getCurrentLocale(), $this->getFallbackLocale()];

        return array_unique($locales);
    }

    /**
     * Get translatable locales for a specific model.
     */
    public function getModelLocales(string | Model $model): array
    {
        $modelClass = is_string($model) ? $model : get_class($model);

        if (! $this->isTranslatable($modelClass)) {
            return [$this->getCurrentLocale()];
        }

        // Get locales from model if it has a method to define them
        if (method_exists($modelClass, 'getTranslatableLocales')) {
            return $modelClass::getTranslatableLocales();
        }

        // Use application available locales
        return $this->getAvailableLocales();
    }

    /**
     * Get translatable attributes for a model.
     */
    public function getTranslatableAttributes(string | Model $model): array
    {
        $modelClass = is_string($model) ? $model : get_class($model);

        if (! $this->isTranslatable($modelClass)) {
            return [];
        }

        $instance = is_string($model) ? new $modelClass : $model;

        if (property_exists($instance, 'translatable')) {
            return $instance->translatable;
        }

        return [];
    }

    /**
     * Check if a model is translatable.
     */
    public function isTranslatable(string | Model $model): bool
    {
        $modelClass = is_string($model) ? $model : get_class($model);

        return in_array(HasTranslations::class, class_uses_recursive($modelClass));
    }

    /**
     * Get the best available locale for displaying a value.
     *
     * Priority: current locale -> fallback locale -> first available translation
     */
    public function getBestLocaleForDisplay(array $translations, ?string $preferredLocale = null): ?string
    {
        $preferredLocale = $preferredLocale ?? $this->getCurrentLocale();

        // Check preferred locale first
        if (isset($translations[$preferredLocale]) && ! empty($translations[$preferredLocale])) {
            return $preferredLocale;
        }

        // Check fallback locale
        $fallbackLocale = $this->getFallbackLocale();
        if (isset($translations[$fallbackLocale]) && ! empty($translations[$fallbackLocale])) {
            return $fallbackLocale;
        }

        // Return first available translation
        foreach ($translations as $locale => $value) {
            if (! empty($value)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Resolve search locales based on configuration and model.
     */
    public function resolveSearchLocales(string | Model $model, ?array $customLocales = null): array
    {
        if ($customLocales !== null) {
            return $customLocales;
        }

        return $this->getModelLocales($model);
    }
}
