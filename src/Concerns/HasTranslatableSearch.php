<?php

namespace Xoshbin\TranslatableSelect\Concerns;

use Xoshbin\TranslatableSelect\Services\TranslatableSearchService;
use Xoshbin\TranslatableSelect\Services\LocaleResolver;

/**
 * Trait for models that want to provide translatable search functionality.
 * 
 * This trait provides a clean interface for models to integrate with
 * the new translatable search system while maintaining backward compatibility.
 */
trait HasTranslatableSearch
{
    /**
     * Get the translatable fields that should be searched.
     * Override this method in your model to customize searchable fields.
     */
    public function getTranslatableSearchFields(): array
    {
        return $this->translatable ?? ['name'];
    }

    /**
     * Get the non-translatable fields that should be searched.
     * Override this method in your model to include additional searchable fields.
     */
    public function getNonTranslatableSearchFields(): array
    {
        return [];
    }

    /**
     * Get search results for Filament select components.
     * 
     * @deprecated Use TranslatableSelect component instead
     */
    public static function getFilamentSearchResults(
        string $search,
        int $limit = 50,
        ?string $labelField = null,
        ?array $searchFields = null
    ): array {
        $service = app(TranslatableSearchService::class);
        
        $options = [
            'searchFields' => $searchFields,
            'labelField' => $labelField ?? 'name',
            'limit' => $limit,
        ];

        return $service->getFilamentSearchResults(static::class, $search, $options);
    }

    /**
     * Get formatted search results with additional context.
     * 
     * @deprecated Use TranslatableSelect component with formatter instead
     */
    public static function getFormattedSearchResults(
        string $search,
        int $limit = 50,
        ?callable $formatter = null,
        ?array $searchFields = null
    ): array {
        $service = app(TranslatableSearchService::class);
        
        $options = [
            'searchFields' => $searchFields,
            'formatter' => $formatter,
            'limit' => $limit,
        ];

        return $service->getFilamentSearchResults(static::class, $search, $options);
    }

    /**
     * Search for models and return a collection with translated labels.
     * 
     * @deprecated Use TranslatableSearchService directly
     */
    public static function searchWithTranslatedLabels(
        string $search,
        int $limit = 50,
        ?array $searchFields = null
    ): \Illuminate\Database\Eloquent\Collection {
        $service = app(TranslatableSearchService::class);
        
        $options = [
            'searchFields' => $searchFields,
            'limit' => $limit,
        ];

        $results = $service->search(static::class, $search, $options);

        return $results->map(function ($model) use ($service) {
            $model->setAttribute('translated_label', $service->getTranslatedLabel($model, 'name'));
            return $model;
        });
    }

    /**
     * Scope to search across all translation locales for translatable fields.
     * 
     * @deprecated Use TranslatableSearchService directly
     */
    public function scopeSearchTranslatable(\Illuminate\Database\Eloquent\Builder $query, string $search, ?array $fields = null): \Illuminate\Database\Eloquent\Builder
    {
        if (empty($search)) {
            return $query;
        }

        $service = app(TranslatableSearchService::class);
        
        $options = [
            'searchFields' => $fields,
        ];

        $results = $service->search(static::class, $search, $options);
        
        return $query->whereIn('id', $results->pluck('id'));
    }

    /**
     * Get the translated label for a field in the current locale.
     */
    public function getTranslatedLabel(string $field, ?string $locale = null): string
    {
        $service = app(TranslatableSearchService::class);
        return $service->getTranslatedLabel($this, $field, $locale);
    }

    /**
     * Get all available translations for a specific field.
     */
    public function getAllTranslations(string $field): array
    {
        $service = app(TranslatableSearchService::class);
        $localeResolver = app(LocaleResolver::class);
        
        if (!in_array($field, $this->translatable ?? [])) {
            return [$field => $this->$field];
        }

        $translations = [];
        foreach ($localeResolver->getAvailableLocales() as $locale) {
            $translation = $service->getTranslatedLabel($this, $field, $locale);
            if ($translation) {
                $translations[$locale] = $translation;
            }
        }

        return $translations;
    }
}
