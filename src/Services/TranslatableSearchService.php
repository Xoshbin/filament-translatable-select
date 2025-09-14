<?php

namespace Xoshbin\TranslatableSelect\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Service for handling translatable search functionality.
 *
 * This service provides the core business logic for searching across
 * translatable fields in models that use Spatie Laravel Translatable.
 */
class TranslatableSearchService
{
    public function __construct(
        private LocaleResolver $localeResolver
    ) {}

    /**
     * Search for models across all translation locales.
     */
    public function search(
        string $modelClass,
        string $search,
        array $options = []
    ): Collection {
        if (empty($search)) {
            return new Collection();
        }

        $query = $this->buildSearchQuery($modelClass, $search, $options);

        $limit = $options['limit'] ?? config('translatable-select.default_limit', 50);

        return $query->limit($limit)->get();
    }

    /**
     * Get search results formatted for Filament select components.
     */
    public function getFilamentSearchResults(
        string $modelClass,
        string $search,
        array $options = []
    ): array {
        $results = $this->search($modelClass, $search, $options);

        $labelField = $options['labelField'] ?? config('translatable-select.component_defaults.label_field', 'name');
        $formatter = $options['formatter'] ?? null;

        return $results->mapWithKeys(function ($model) use ($labelField, $formatter) {
            if ($formatter && is_callable($formatter)) {
                $formatted = $formatter($model);
                return is_array($formatted) ? $formatted : [$model->id => $formatted];
            }

            $label = $this->getTranslatedLabel($model, $labelField);
            return [$model->id => $label];
        })->toArray();
    }

    /**
     * Get the translated label for a field in the current locale.
     */
    public function getTranslatedLabel(Model $model, string $field, ?string $locale = null): string
    {
        $locale = $locale ?? $this->localeResolver->getCurrentLocale();

        // Check if the field is translatable and the model has the HasTranslations trait
        if ($this->isFieldTranslatable($model, $field) && $this->hasTranslationsSupport($model)) {
            $translation = $model->getTranslation($field, $locale);
            return $translation ?: ($model->$field ?? '');
        }

        // Return the field value directly for non-translatable fields
        return $model->$field ?? '';
    }

    /**
     * Get the translatable fields for a model.
     */
    public function getTranslatableFields(string $modelClass): array
    {
        $model = new $modelClass;

        if (!$this->hasTranslationsSupport($model)) {
            return [];
        }

        return $model->translatable ?? [];
    }

    /**
     * Get the searchable translatable fields for a model.
     */
    public function getSearchableTranslatableFields(string $modelClass): array
    {
        $model = new $modelClass;

        // Check if model has custom method for searchable fields
        if (method_exists($model, 'getTranslatableSearchFields')) {
            return $model->getTranslatableSearchFields();
        }

        // Fallback to all translatable fields
        return $this->getTranslatableFields($modelClass);
    }

    /**
     * Get the searchable non-translatable fields for a model.
     */
    public function getSearchableNonTranslatableFields(string $modelClass): array
    {
        $model = new $modelClass;

        // Check if model has custom method for non-translatable searchable fields
        if (method_exists($model, 'getNonTranslatableSearchFields')) {
            return $model->getNonTranslatableSearchFields();
        }

        return [];
    }

    /**
     * Build the search query for a model.
     */
    private function buildSearchQuery(string $modelClass, string $search, array $options): Builder
    {
        $query = $modelClass::query();

        $translatableFields = $options['searchFields'] ?? $this->getSearchableTranslatableFields($modelClass);
        $nonTranslatableFields = $this->getSearchableNonTranslatableFields($modelClass);
        $locales = $this->localeResolver->getAvailableLocales();

        return $query->where(function (Builder $subQuery) use ($search, $translatableFields, $nonTranslatableFields, $locales) {
            // Search in translatable fields across all locales
            if (!empty($translatableFields)) {
                foreach ($translatableFields as $field) {
                    foreach ($locales as $locale) {
                        $this->addTranslatableFieldCondition($subQuery, $field, $locale, $search);
                    }
                }
            }

            // Search in non-translatable fields
            foreach ($nonTranslatableFields as $field) {
                $subQuery->orWhere($field, 'LIKE', '%' . $search . '%');
            }
        });
    }

    /**
     * Add a translatable field search condition to the query.
     */
    private function addTranslatableFieldCondition(Builder $query, string $field, string $locale, string $search): void
    {
        $dbDriver = config('database.default');
        $jsonExtractions = config('translatable-select.database.json_extraction', []);

        // Get the appropriate JSON extraction pattern
        $pattern = $jsonExtractions[$dbDriver] ?? $jsonExtractions['mysql'] ?? 'LOWER(JSON_UNQUOTE(JSON_EXTRACT(`{field}`, "$.{locale}"))) LIKE ?';

        // Replace placeholders
        $sql = str_replace(['{field}', '{locale}'], [$field, $locale], $pattern);

        // Prepare search term
        $searchTerm = config('translatable-select.database.case_insensitive', true)
            ? '%' . strtolower($search) . '%'
            : '%' . $search . '%';

        $query->orWhereRaw($sql, [$searchTerm]);
    }

    /**
     * Check if a field is translatable for a model.
     */
    private function isFieldTranslatable(Model $model, string $field): bool
    {
        return in_array($field, $model->translatable ?? [], true);
    }

    /**
     * Check if the model has translation support.
     */
    private function hasTranslationsSupport(Model $model): bool
    {
        return in_array(HasTranslations::class, class_uses_recursive($model), true);
    }
}
