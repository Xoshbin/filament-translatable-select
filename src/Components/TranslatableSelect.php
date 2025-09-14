<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect\Components;

use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use Xoshbin\TranslatableSelect\Services\LocaleResolver;
use Xoshbin\TranslatableSelect\Services\TranslatableSearchService;

/**
 * A Filament Select component with built-in translatable search functionality.
 *
 * This component extends Filament's Select to provide seamless integration
 * with Spatie Laravel Translatable models, offering cross-locale search
 * capabilities while maintaining full compatibility with all Select features.
 */
class TranslatableSelect extends Select
{
    protected ?string $relationshipName = null;
    protected string|Closure|null $relationshipTitleAttribute = null;
    protected ?string $modelClass = null;
    protected array $searchableFields = [];
    protected ?array $searchLocales = null;
    protected ?string $fallbackLocale = null;
    protected ?Closure $queryModifier = null;

    /**
     * Create a translatable select for an Eloquent relationship.
     */
    public function relationship(
        Closure|string|null $name = null,
        Closure|string|null $titleAttribute = null,
        ?Closure $modifyQueryUsing = null,
        bool $ignoreRecord = false
    ): static {
        // Call parent first to set up the basic relationship
        parent::relationship($name, $titleAttribute, $modifyQueryUsing, $ignoreRecord);

        // Store our custom properties
        $this->relationshipName = $name;
        $this->relationshipTitleAttribute = $titleAttribute ?? 'name';

        if ($modifyQueryUsing) {
            $this->queryModifier = $modifyQueryUsing;
        }

        $this->configureForRelationship();

        return $this;
    }

    /**
     * Set the fields to search in (overrides auto-detection).
     */
    public function searchableFields(array $fields): static
    {
        $this->searchableFields = $fields;

        return $this;
    }

    /**
     * Set the locales to search in (overrides auto-detection).
     */
    public function searchLocales(array $locales): static
    {
        $this->searchLocales = $locales;

        return $this;
    }

    /**
     * Set the fallback locale for label display.
     */
    public function fallbackLocale(string $locale): static
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * Modify the query used for searching and preloading.
     */
    public function modifyQueryUsing(?callable $modifier): static
    {
        $this->queryModifier = $modifier;

        return $this;
    }

    /**
     * Configure the component for relationship usage.
     */
    protected function configureForRelationship(): void
    {
        $this->getSearchResultsUsing(function (string $search) {
            return $this->getTranslatableSearchResults($search);
        });

        $this->getOptionLabelUsing(function ($value) {
            return $this->getTranslatableOptionLabel($value);
        });

        $this->getOptionLabelsUsing(function (array $values) {
            return $this->getTranslatableOptionLabels($values);
        });

        // Configure options for preloading
        $this->options(function () {
            if (! $this->isPreloaded()) {
                return [];
            }

            return $this->getPreloadedOptions();
        });
    }

    /**
     * Get search results using translatable search service.
     */
    protected function getTranslatableSearchResults(string $search): array
    {
        $modelClass = $this->getRelatedModelClass();

        if (! $modelClass) {
            return [];
        }

        $searchService = app(TranslatableSearchService::class);
        $localeResolver = app(LocaleResolver::class);

        $searchFields = ! empty($this->searchableFields)
            ? $this->searchableFields
            : $localeResolver->getTranslatableAttributes($modelClass);

        if (empty($searchFields)) {
            $searchFields = [$this->relationshipTitleAttribute ?? 'name'];
        }

        $searchLocales = $this->searchLocales ?? $localeResolver->getModelLocales($modelClass);

        return $searchService->getFilamentSearchResults($modelClass, $search, [
            'searchFields' => $searchFields,
            'labelField' => $this->relationshipTitleAttribute ?? 'name',
            'searchLocales' => $searchLocales,
            'queryModifier' => $this->queryModifier,
            'limit' => 50,
        ]);
    }

    /**
     * Get translatable option label for a single value.
     */
    protected function getTranslatableOptionLabel($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $modelClass = $this->getRelatedModelClass();

        if (! $modelClass) {
            return null;
        }

        $model = $modelClass::find($value);

        if (! $model) {
            return null;
        }

        $searchService = app(TranslatableSearchService::class);

        return $searchService->getTranslatedLabel(
            $model,
            $this->relationshipTitleAttribute ?? 'name',
            $this->fallbackLocale
        );
    }

    /**
     * Get translatable option labels for multiple values.
     */
    protected function getTranslatableOptionLabels(array $values): array
    {
        if (empty($values)) {
            return [];
        }

        $modelClass = $this->getRelatedModelClass();

        if (! $modelClass) {
            return [];
        }

        $models = $modelClass::whereIn('id', $values)->get();
        $searchService = app(TranslatableSearchService::class);

        return $searchService->getTranslatedLabels(
            $models,
            $this->relationshipTitleAttribute ?? 'name',
            $this->fallbackLocale
        );
    }

    /**
     * Get preloaded options for the select.
     */
    protected function getPreloadedOptions(): array
    {
        $modelClass = $this->getRelatedModelClass();

        if (! $modelClass) {
            return [];
        }

        $searchService = app(TranslatableSearchService::class);

        return $searchService->preloadOptions(
            $modelClass,
            $this->relationshipTitleAttribute ?? 'name',
            $this->queryModifier,
            50
        );
    }

    /**
     * Get the related model class from the relationship.
     */
    protected function getRelatedModelClass(): ?string
    {
        if ($this->modelClass) {
            return $this->modelClass;
        }

        if (! $this->relationshipName) {
            return null;
        }

        $record = $this->getRecord();

        if (! $record) {
            // Try to get from Livewire component
            $livewire = $this->getLivewire();

            if (method_exists($livewire, 'getModel')) {
                $record = app($livewire->getModel());
            } elseif (property_exists($livewire, 'model')) {
                $record = app($livewire->model);
            }
        }

        if (! $record instanceof Model) {
            return null;
        }

        $relationship = $record->{$this->relationshipName}();

        if (! $relationship instanceof Relation) {
            return null;
        }

        $this->modelClass = get_class($relationship->getRelated());

        return $this->modelClass;
    }

    /**
     * Check if the select should preload options.
     */
    public function isPreloaded(): bool
    {
        return $this->evaluate($this->isPreloaded) ?? false;
    }

    /**
     * Create a translatable select for a specific model class (non-relationship).
     */
    public static function forModel(
        string $name,
        string $modelClass,
        string $labelField = 'name',
        ?callable $modifyQueryUsing = null
    ): static {
        $static = static::make($name);

        $static->modelClass = $modelClass;
        $static->relationshipTitleAttribute = $labelField;

        if ($modifyQueryUsing) {
            $static->queryModifier = $modifyQueryUsing;
        }

        $static->configureForModel();

        return $static;
    }

    /**
     * Configure the component for direct model usage (non-relationship).
     */
    protected function configureForModel(): void
    {
        $this->getSearchResultsUsing(function (string $search) {
            return $this->getTranslatableSearchResults($search);
        });

        $this->getOptionLabelUsing(function ($value) {
            return $this->getTranslatableOptionLabel($value);
        });

        $this->getOptionLabelsUsing(function (array $values) {
            return $this->getTranslatableOptionLabels($values);
        });

        // Configure options for preloading
        $this->options(function () {
            if (! $this->isPreloaded()) {
                return [];
            }

            return $this->getPreloadedOptions();
        });
    }

    /**
     * Enable preloading with translatable support.
     */
    public function preload(bool|Closure $condition = true): static
    {
        parent::preload($condition);

        return $this;
    }

    /**
     * Enable multiple selection with translatable support.
     */
    public function multiple(bool|Closure $condition = true): static
    {
        parent::multiple($condition);

        return $this;
    }

    /**
     * Enable searchable functionality (always enabled for translatable selects).
     */
    public function searchable(bool|array|Closure $condition = true): static
    {
        parent::searchable($condition);

        return $this;
    }

    /**
     * Set custom search debounce for better performance.
     */
    public function searchDebounce(int|Closure $debounce): static
    {
        parent::searchDebounce($debounce);

        return $this;
    }

    /**
     * Set the search prompt message.
     */
    public function searchPrompt(Htmlable|string|Closure|null $message): static
    {
        parent::searchPrompt($message);

        return $this;
    }

    /**
     * Set the searching message.
     */
    public function searchingMessage(Htmlable|string|Closure|null $message): static
    {
        parent::searchingMessage($message);

        return $this;
    }

    /**
     * Set the no search results message.
     */
    public function noSearchResultsMessage(Htmlable|string|Closure|null $message): static
    {
        parent::noSearchResultsMessage($message);

        return $this;
    }

    /**
     * Get the model class, with validation.
     */
    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    /**
     * Get the searchable fields.
     */
    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }

    /**
     * Get the search locales.
     */
    public function getSearchLocales(): ?array
    {
        return $this->searchLocales;
    }

    /**
     * Get the fallback locale.
     */
    public function getFallbackLocale(): ?string
    {
        return $this->fallbackLocale;
    }

    /**
     * Get the relationship name.
     */
    public function getRelationshipName(): ?string
    {
        return $this->relationshipName;
    }

    /**
     * Get the relationship title attribute.
     */
    public function getRelationshipTitleAttribute(): ?string
    {
        return $this->evaluate($this->relationshipTitleAttribute);
    }

    /**
     * Get the search results closure.
     */
    public function getSearchResultsClosure(): ?Closure
    {
        return function (string $search) {
            return $this->getTranslatableSearchResults($search);
        };
    }

    /**
     * Get the option label closure.
     */
    public function getOptionLabelClosure(): ?Closure
    {
        return function ($value) {
            return $this->getTranslatableOptionLabel($value);
        };
    }

    /**
     * Get the option labels closure.
     */
    public function getOptionLabelsClosure(): ?Closure
    {
        return function (array $values) {
            return $this->getTranslatableOptionLabels($values);
        };
    }

    /**
     * Get the options closure.
     */
    public function getOptionsClosure(): ?Closure
    {
        return function () {
            if (! $this->isPreloaded()) {
                return [];
            }

            return $this->getPreloadedOptions();
        };
    }

    /**
     * Get the helper text.
     */
    public function getHelperText(): ?string
    {
        return parent::getHelperText();
    }

    /**
     * Get the placeholder.
     */
    public function getPlaceholder(): ?string
    {
        return parent::getPlaceholder();
    }

    /**
     * Get the label.
     */
    public function getLabel(): ?string
    {
        return parent::getLabel();
    }

    /**
     * Check if the field is required.
     */
    public function isRequired(): bool
    {
        return parent::isRequired();
    }

    /**
     * Check if the field is disabled.
     */
    public function isDisabled(): bool
    {
        return parent::isDisabled();
    }

    /**
     * Check if the field is multiple.
     */
    public function isMultiple(): bool
    {
        return parent::isMultiple();
    }

    /**
     * Check if the field is searchable.
     */
    public function isSearchable(): bool
    {
        return parent::isSearchable();
    }
}
