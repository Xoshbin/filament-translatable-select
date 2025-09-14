<?php

namespace Xoshbin\TranslatableSelect\Components;

use Xoshbin\TranslatableSelect\Services\TranslatableSearchService;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * A Filament Select component with built-in translatable search functionality.
 *
 * This component extends Filament's Select to provide seamless integration
 * with Spatie Laravel Translatable models, offering multi-locale search
 * capabilities with automatic field detection and dynamic locale resolution.
 */
class TranslatableSelect extends Select
{
    protected string $modelClass;
    protected array $searchFields = [];
    protected $formatter = null;
    protected string $labelField;
    protected int $searchLimit;
    protected bool $isPreloadEnabled = false;
    protected $queryModifier = null;

    /**
     * Create a new TranslatableSelect component.
     */
    public static function make(?string $name = null): static
    {
        $static = parent::make($name);

        // Set defaults from configuration
        $static->labelField = config('translatable-select.component_defaults.label_field', 'name');
        $static->searchLimit = config('translatable-select.component_defaults.search_limit', 50);

        if (config('translatable-select.component_defaults.searchable', true)) {
            $static->searchable();
        }

        return $static;
    }

    /**
     * Set the model class for this select component.
     */
    public function modelClass(string $modelClass): static
    {
        if (!class_exists($modelClass)) {
            throw new InvalidArgumentException("Model class {$modelClass} does not exist.");
        }

        if (!is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("Class {$modelClass} must extend Illuminate\\Database\\Eloquent\\Model.");
        }

        $this->modelClass = $modelClass;

        // Configure the component now that we have the model class
        $this->configureSearchResults();
        $this->configureOptionLabels();

        return $this;
    }

    /**
     * Set the fields to search in (overrides auto-detection).
     */
    public function searchFields(array $fields): static
    {
        $this->searchFields = $fields;

        return $this;
    }

    /**
     * Set a custom formatter for option labels.
     */
    public function formatter(callable $formatter): static
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Set the field to use for option labels.
     */
    public function labelField(string $field): static
    {
        $this->labelField = $field;

        return $this;
    }

    /**
     * Set the maximum number of search results.
     */
    public function searchLimit(int $limit): static
    {
        $this->searchLimit = $limit;

        return $this;
    }

    /**
     * Enable preloading of options.
     */
    public function preload(\Closure|bool $condition = true): static
    {
        $this->isPreloadEnabled = is_bool($condition) ? $condition : true;

        return parent::preload($condition);
    }

    /**
     * Configure the component after instantiation.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Configuration will be done when model class is set
    }

    /**
     * Configure the search results functionality.
     */
    private function configureSearchResults(): void
    {
        $this->getSearchResultsUsing(function (string $search): array {
            $service = app(TranslatableSearchService::class);

            $options = [
                'searchFields' => !empty($this->searchFields) ? $this->searchFields : null,
                'labelField' => $this->labelField,
                'formatter' => $this->formatter,
                'limit' => $this->searchLimit,
            ];

            if ($this->queryModifier) {
                // Apply query modifier to search results
                $results = $service->search($this->modelClass, $search, $options);

                // Apply query modifier
                $query = $this->modelClass::whereIn('id', $results->pluck('id'));
                $query = ($this->queryModifier)($query);
                $filteredResults = $query->get();

                return $filteredResults->mapWithKeys(function ($model) use ($service) {
                    if ($this->formatter) {
                        $formatted = ($this->formatter)($model);
                        return is_array($formatted) ? $formatted : [$model->id => $formatted];
                    }

                    $label = $service->getTranslatedLabel($model, $this->labelField);
                    return [$model->id => $label];
                })->toArray();
            }

            return $service->getFilamentSearchResults($this->modelClass, $search, $options);
        });
    }

    /**
     * Configure the option label functionality.
     */
    private function configureOptionLabels(): void
    {
        // Set up options - load actual options if preload is enabled
        $this->options(function (): array {
            if (!$this->isPreloadEnabled) {
                // Return empty array for search-only behavior
                return [];
            }

            // Load options for preload
            $service = app(TranslatableSearchService::class);

            // Start with base query
            $query = $this->modelClass::query();

            // Apply query modifier if present
            if ($this->queryModifier) {
                $query = ($this->queryModifier)($query);
            }

            // Limit results for performance
            $models = $query->limit($this->searchLimit)->get();

            return $models->mapWithKeys(function ($model) use ($service) {
                if ($this->formatter) {
                    $formatted = ($this->formatter)($model);
                    return is_array($formatted) ? $formatted : [$model->id => $formatted];
                }

                $label = $service->getTranslatedLabel($model, $this->labelField);
                return [$model->id => $label];
            })->toArray();
        });

        $this->getOptionLabelUsing(function ($value): ?string {
            if ($value === null) {
                return null;
            }

            $model = $this->modelClass::find($value);

            if (!$model) {
                return null;
            }

            if ($this->formatter) {
                $formatted = ($this->formatter)($model);
                return is_array($formatted) ? $formatted[$model->id] ?? null : $formatted;
            }

            $service = app(TranslatableSearchService::class);
            return $service->getTranslatedLabel($model, $this->labelField);
        });
    }



    /**
     * Create a select with a custom query modifier.
     */
    public function modifyQueryUsing(callable $modifier): static
    {
        $this->queryModifier = $modifier;

        // Reconfigure the component with the new query modifier
        $this->configureSearchResults();
        $this->configureOptionLabels();

        return $this;
    }



    /**
     * Get the model class.
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Get the search fields.
     */
    public function getSearchFields(): array
    {
        return $this->searchFields;
    }

    /**
     * Get the formatter.
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Get the label field.
     */
    public function getLabelField(): string
    {
        return $this->labelField;
    }

    /**
     * Get the search limit.
     */
    public function getSearchLimit(): int
    {
        return $this->searchLimit;
    }
}
