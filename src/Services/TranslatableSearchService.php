<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Service for handling cross-locale search functionality.
 */
class TranslatableSearchService
{
    public function __construct(
        protected LocaleResolver $localeResolver
    ) {}

    /**
     * Search across multiple locales for translatable models.
     */
    public function searchAcrossLocales(
        string $modelClass,
        string $search,
        array $searchFields,
        array $searchLocales,
        ?callable $queryModifier = null,
        int $limit = 50
    ): Collection {
        $query = $modelClass::query();

        // Apply custom query modifications first
        if ($queryModifier) {
            $query = $queryModifier($query);
        }

        // Build cross-locale search conditions
        $this->addCrossLocaleSearchConditions($query, $search, $searchFields, $searchLocales);

        return $query->limit($limit)->get();
    }

    /**
     * Add cross-locale search conditions to a query.
     */
    protected function addCrossLocaleSearchConditions(
        Builder $query,
        string $search,
        array $searchFields,
        array $searchLocales
    ): void {
        $modelClass = $query->getModel();
        $translatableFields = $this->localeResolver->getTranslatableAttributes($modelClass);
        $nonTranslatableFields = method_exists($modelClass, 'getNonTranslatableSearchFields')
            ? $modelClass->getNonTranslatableSearchFields()
            : [];

        $query->where(function (Builder $subQuery) use ($search, $searchFields, $searchLocales, $translatableFields, $nonTranslatableFields) {
            foreach ($searchFields as $field) {
                if (in_array($field, $translatableFields)) {
                    // Handle translatable fields - search across all locales
                    foreach ($searchLocales as $locale) {
                        $this->addTranslatableFieldCondition($subQuery, $field, $locale, $search);
                    }
                } elseif (in_array($field, $nonTranslatableFields) || !in_array($field, $translatableFields)) {
                    // Handle non-translatable fields - simple LIKE search
                    $this->addNonTranslatableFieldCondition($subQuery, $field, $search);
                }
            }
        });
    }

    /**
     * Add a search condition for a translatable field and locale.
     */
    protected function addTranslatableFieldCondition(
        Builder $query,
        string $field,
        string $locale,
        string $search
    ): void {
        $connection = $query->getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $query->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT({$field}, ?))) LIKE LOWER(?)",
                ["$.{$locale}", "%{$search}%"]
            );
        } elseif ($driver === 'pgsql') {
            $query->orWhereRaw(
                "({$field}->?)::text ILIKE ?",
                [$locale, "%{$search}%"]
            );
        } else {
            // Fallback for other databases - cast to text and search
            $query->orWhereRaw(
                "CAST({$field} AS TEXT) LIKE ?",
                ["%{$search}%"]
            );
        }
    }

    /**
     * Add a search condition for a non-translatable field.
     */
    protected function addNonTranslatableFieldCondition(
        Builder $query,
        string $field,
        string $search
    ): void {
        $connection = $query->getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $query->orWhereRaw("LOWER({$field}) LIKE LOWER(?)", ["%{$search}%"]);
        } elseif ($driver === 'pgsql') {
            $query->orWhere($field, 'ILIKE', "%{$search}%");
        } else {
            // Fallback for other databases
            $query->orWhere($field, 'LIKE', "%{$search}%");
        }
    }

    /**
     * Get the translated label for a model field.
     */
    public function getTranslatedLabel(Model $model, string $field, ?string $preferredLocale = null): string
    {
        if (! $this->localeResolver->isTranslatable($model)) {
            return (string) $model->getAttribute($field);
        }

        $translations = $model->getTranslations($field);

        if (empty($translations)) {
            return (string) $model->getAttribute($field);
        }

        $bestLocale = $this->localeResolver->getBestLocaleForDisplay($translations, $preferredLocale);

        if ($bestLocale && isset($translations[$bestLocale])) {
            return (string) $translations[$bestLocale];
        }

        return (string) $model->getAttribute($field);
    }

    /**
     * Get search results formatted for Filament Select component.
     */
    public function getFilamentSearchResults(
        string $modelClass,
        string $search,
        array $options = []
    ): array {
        $searchFields = $options['searchFields'] ?? $this->getDefaultSearchFields($modelClass);
        $labelField = $options['labelField'] ?? 'name';
        $searchLocales = $options['searchLocales'] ?? $this->localeResolver->getModelLocales($modelClass);
        $queryModifier = $options['queryModifier'] ?? null;
        $limit = $options['limit'] ?? 50;

        $results = $this->searchAcrossLocales(
            $modelClass,
            $search,
            $searchFields,
            $searchLocales,
            $queryModifier,
            $limit
        );

        return $results->mapWithKeys(function (Model $model) use ($labelField, $options) {
            $label = $this->getTranslatedLabel($model, $labelField);

            // Apply custom formatter if provided
            if (isset($options['formatter']) && is_callable($options['formatter'])) {
                $formatted = ($options['formatter'])($model);
                if (is_array($formatted)) {
                    return $formatted;
                }
                $label = $formatted;
            }

            return [$model->getKey() => $label];
        })->toArray();
    }

    /**
     * Get default search fields for a model.
     */
    protected function getDefaultSearchFields(string $modelClass): array
    {
        $translatableFields = $this->localeResolver->getTranslatableAttributes($modelClass);

        if (! empty($translatableFields)) {
            return $translatableFields;
        }

        // Fallback to common field names
        return ['name'];
    }

    /**
     * Get multiple translated labels efficiently.
     */
    public function getTranslatedLabels(Collection | SupportCollection $models, string $field, ?string $preferredLocale = null): array
    {
        return $models->mapWithKeys(function (Model $model) use ($field, $preferredLocale) {
            return [$model->getKey() => $this->getTranslatedLabel($model, $field, $preferredLocale)];
        })->toArray();
    }

    /**
     * Preload translatable options for a model.
     */
    public function preloadOptions(
        string $modelClass,
        string $labelField,
        ?callable $queryModifier = null,
        int $limit = 50
    ): array {
        $query = $modelClass::query();

        if ($queryModifier) {
            $query = $queryModifier($query);
        }

        $models = $query->limit($limit)->get();

        return $this->getTranslatedLabels($models, $labelField);
    }
}
