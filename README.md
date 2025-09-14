# Filament TranslatableSelect

[![Latest Version on Packagist](https://img.shields.io/packagist/v/xoshbin/translatable-select.svg?style=flat-square)](https://packagist.org/packages/xoshbin/translatable-select)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/xoshbin/translatable-select/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/xoshbin/translatable-select/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/xoshbin/translatable-select/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/xoshbin/translatable-select/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/xoshbin/translatable-select.svg?style=flat-square)](https://packagist.org/packages/xoshbin/translatable-select)

A powerful Filament Select component that extends Filament's native Select to work seamlessly with **Spatie Laravel Translatable** package. This component provides multi-locale search capabilities across translatable fields with automatic field detection, dynamic locale resolution, and advanced configuration options.

## 🚀 Key Features

- **Multi-Locale Search**: Search across all translatable fields in multiple locales simultaneously
- **Automatic Field Detection**: Automatically detects translatable fields from your models
- **Custom Search Configuration**: Override searchable fields, label fields, and formatting
- **Performance Optimized**: Configurable search limits and efficient database queries
- **Query Modification**: Apply custom query constraints and filters
- **Advanced Formatting**: Custom formatters for complex display requirements
- **Locale Resolution**: Intelligent locale detection from Filament plugins and app configuration
- **Database Agnostic**: Supports MySQL, PostgreSQL, and SQLite with optimized JSON queries

## 📋 Requirements

- **PHP**: ^8.1
- **Laravel**: ^10.0|^11.0|^12.0
- **Filament**: ^3.0|^4.0
- **Spatie Laravel Translatable**: ^6.0
- **Lara Zeus Spatie Translatable**: ^1.0 (for Filament integration)

## 📦 Installation

Install the package via Composer:

```bash
composer require xoshbin/translatable-select
```

### Publish Configuration (Optional)

You can publish the configuration file to customize default settings:

```bash
php artisan vendor:publish --tag="translatable-select-config"
```

This will publish the configuration file to `config/translatable-select.php` where you can customize:

- Default search limits
- Locale resolution strategies
- Database-specific JSON extraction patterns
- Component default settings

## 🎯 Why `modelClass()` Instead of Filament's `relationship()`?

The `TranslatableSelect` component uses a custom `modelClass()` method instead of Filament's standard `relationship()` method for several important reasons:

### **`modelClass()` is NOT part of Filament**
The `modelClass()` method is **custom functionality** created specifically for this component to handle translatable search requirements that Filament's standard `relationship()` method cannot provide.

### **Key Differences:**

#### **Standard Filament `relationship()` Method:**
```php
// Standard Filament approach - limited to simple relationships
Forms\Components\Select::make('user_id')
    ->label('Author')
    ->relationship('author', 'name')  // Uses Eloquent relationship
    ->required()
```

#### **TranslatableSelect `modelClass()` Method:**
```php
// Custom TranslatableSelect approach - enables translatable search
TranslatableSelect::make('currency_id')
    ->modelClass(Currency::class)     // Direct model class reference
    ->label('Currency')
    ->searchFields(['name'])          // Searches 'name' in all locales
```

### **Why `modelClass()` Was Chosen:**

1. **Multi-Locale Search Requirements**: The component needs to search across translatable fields in multiple locales (en, ar, ckb, etc.) simultaneously
2. **Custom Search Logic**: Implements specialized search service (`TranslatableSearchService`) for translatable field queries
3. **Flexibility Beyond Relationships**: Works with any model class, not just those with defined Eloquent relationships
4. **Advanced Configuration**: Provides features like custom search fields, formatters, and query modifiers that standard relationships don't offer

### **When to Use Each:**

**Use Standard Filament `relationship()`:**
- Simple, non-translatable models
- When you have a defined Eloquent relationship
- Basic select functionality without multi-locale requirements

**Use TranslatableSelect `modelClass()`:**
- Translatable models with multi-locale search needs
- Advanced search configuration requirements
- Complex search scenarios across multiple fields and locales

## 🔧 Basic Usage

### Simple Translatable Select

The most basic usage with automatic translatable field detection:

```php
use Xoshbin\TranslatableSelect\Components\TranslatableSelect;
use App\Models\Currency;

TranslatableSelect::make('currency_id')
    ->modelClass(Currency::class)
    ->label('Currency')
```

### Comparison with Standard Filament Select

**Standard Filament Select:**
```php
// Limited to simple relationships, no multi-locale search
Forms\Components\Select::make('currency_id')
    ->relationship('currency', 'name')
    ->searchable()
    ->required()
```

**TranslatableSelect:**
```php
// Multi-locale search across translatable fields
TranslatableSelect::make('currency_id')
    ->modelClass(Currency::class)
    ->label('Currency')
    ->searchFields(['name'])  // Searches in all locales: en, ar, ckb, etc.
    ->required()
```

## ⚙️ Configuration Options

### Custom Search Fields

Override which fields to search in:

```php
TranslatableSelect::make('account_id')
    ->modelClass(Account::class)
    ->label('Account')
    ->searchFields(['name', 'code'])  // Search in both name and code fields
```

### Custom Label Field

Change which field is used for display labels:

```php
TranslatableSelect::make('account_id')
    ->modelClass(Account::class)
    ->label('Account')
    ->labelField('code')  // Display account code instead of name
```

### Custom Formatting

Apply custom formatting to display labels:

```php
TranslatableSelect::make('account_id')
    ->modelClass(Account::class)
    ->label('Account')
    ->formatter(fn($model) => $model->name . ' (' . $model->code . ')')
```

### Search Limits

Control the number of search results:

```php
TranslatableSelect::make('currency_id')
    ->modelClass(Currency::class)
    ->label('Currency')
    ->searchLimit(25)  // Limit to 25 results for performance
```

### Query Modification

Apply custom query constraints:

```php
TranslatableSelect::make('account_id')
    ->modelClass(Account::class)
    ->label('Account')
    ->modifyQueryUsing(fn($query) => $query->where('is_active', true))
```

### Preloading Options

Enable preloading for better UX:

```php
TranslatableSelect::make('currency_id')
    ->modelClass(Currency::class)
    ->label('Currency')
    ->preload()  // Load options immediately
```

## 🏗️ Model Setup

### Basic Model Configuration

Your translatable models should use the `HasTranslations` trait from Spatie Laravel Translatable:

```php
use Spatie\Translatable\HasTranslations;
use Xoshbin\TranslatableSelect\Concerns\HasTranslatableSearch;

class Currency extends Model
{
    use HasTranslations, HasTranslatableSearch;

    public array $translatable = ['name'];

    protected $fillable = ['code', 'name', 'symbol'];
}
```

### Advanced Model Configuration

Customize searchable fields and behavior:

```php
use Spatie\Translatable\HasTranslations;
use Xoshbin\TranslatableSelect\Concerns\HasTranslatableSearch;

class Account extends Model
{
    use HasTranslations, HasTranslatableSearch;

    public array $translatable = ['name', 'description'];

    protected $fillable = ['code', 'name', 'description', 'type'];

    /**
     * Override which translatable fields should be searched
     */
    public function getTranslatableSearchFields(): array
    {
        return ['name']; // Only search in name, not description
    }

    /**
     * Include non-translatable fields in search
     */
    public function getNonTranslatableSearchFields(): array
    {
        return ['code']; // Also search in the code field
    }
}
```

## 🔧 Configuration File

The package comes with a comprehensive configuration file. Here are the key settings:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Search Limit
    |--------------------------------------------------------------------------
    */
    'default_limit' => 50,

    /*
    |--------------------------------------------------------------------------
    | Locale Resolution Strategy
    |--------------------------------------------------------------------------
    | Available strategies: 'auto', 'filament', 'config', 'manual'
    */
    'locale_strategy' => env('TRANSLATABLE_SEARCH_LOCALE_STRATEGY', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Manual Locales (when using 'manual' strategy)
    |--------------------------------------------------------------------------
    */
    'manual_locales' => ['en', 'ckb', 'ar'],

    /*
    |--------------------------------------------------------------------------
    | Component Defaults
    |--------------------------------------------------------------------------
    */
    'component_defaults' => [
        'label_field' => 'name',
        'search_limit' => 50,
        'searchable' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */
    'database' => [
        'case_insensitive' => true,
        'json_extraction' => [
            'sqlite' => 'LOWER(json_extract(`{field}`, "$.{locale}")) LIKE ?',
            'mysql' => 'LOWER(JSON_UNQUOTE(JSON_EXTRACT(`{field}`, "$.{locale}"))) LIKE ?',
            'pgsql' => 'LOWER(({field}->>\'{locale}\')) LIKE ?',
        ],
    ],
];
```

## 🎨 Advanced Examples

### Complex Account Selection with Filtering

```php
TranslatableSelect::make('account_id')
    ->modelClass(Account::class)
    ->label('Account')
    ->searchFields(['name', 'code'])
    ->labelField('name')
    ->formatter(fn($model) => $model->name . ' (' . $model->code . ')')
    ->modifyQueryUsing(fn($query) => $query->where('is_active', true))
    ->searchLimit(100)
    ->preload()
    ->required()
```

### Currency Selection with Custom Display

```php
TranslatableSelect::make('currency_id')
    ->modelClass(Currency::class)
    ->label('Currency')
    ->searchFields(['name'])
    ->formatter(function($model) {
        return $model->name . ' (' . $model->code . ') - ' . $model->symbol;
    })
    ->searchLimit(25)
```

### Partner Selection with Company Filtering

```php
TranslatableSelect::make('partner_id')
    ->modelClass(Partner::class)
    ->label('Partner')
    ->searchFields(['name', 'company_name'])
    ->modifyQueryUsing(function($query) {
        return $query->where('company_id', Filament::getTenant()->id)
                    ->where('is_active', true);
    })
    ->formatter(fn($model) => $model->name . ' - ' . $model->company_name)
    ->searchLimit(50)
```

## 🔍 How It Works

### Multi-Locale Search Process

1. **Locale Detection**: The component automatically detects available locales from:
   - Filament Spatie Translatable plugin configuration
   - Laravel application configuration
   - Manual configuration

2. **Field Detection**: Automatically identifies translatable fields from your model's `$translatable` array

3. **Query Building**: Constructs database queries that search across all locales using JSON extraction:
   ```sql
   -- Example for MySQL
   SELECT * FROM currencies
   WHERE LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.en"))) LIKE '%dollar%'
      OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.ar"))) LIKE '%dollar%'
      OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.ckb"))) LIKE '%dollar%'
   ```

4. **Result Formatting**: Formats results using the current locale or custom formatters

### Performance Considerations

- **Search Limits**: Configurable limits prevent performance issues with large datasets
- **Database Optimization**: Uses efficient JSON extraction queries optimized for each database type
- **Caching**: Locale resolution is cached for better performance
- **Preloading**: Optional preloading for frequently used selects

## 🐛 Troubleshooting

### Common Issues

**1. Component not found**
```bash
# Make sure the service provider is registered
php artisan package:discover
```

**2. No search results**
```php
// Ensure your model has the HasTranslations trait
use Spatie\Translatable\HasTranslations;

class YourModel extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];
}
```

**3. Wrong locale displayed**
```php
// Check Filament plugin configuration
// Verify app.locale configuration
dd(app()->getLocale());
```

**4. Performance issues**
```php
// Reduce search limits
TranslatableSelect::make('field')
    ->searchLimit(25)  // Reduce from default 50

// Add database indexes on translatable JSON fields
// Consider preloading for frequently used selects
```

### Debug Helpers

```php
// Check available locales
dd(app(\Xoshbin\TranslatableSelect\Services\LocaleResolver::class)->getAvailableLocales());

// Test search directly
dd(app(\Xoshbin\TranslatableSelect\Services\TranslatableSearchService::class)
    ->search(Currency::class, 'test'));

// Check model translatable fields
$service = app(\Xoshbin\TranslatableSelect\Services\TranslatableSearchService::class);
dd($service->getTranslatableFields(Currency::class));
```

## 🧪 Testing

```bash
composer test
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🤝 Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## 🔒 Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## 👥 Credits

- [Khoshbin](https://github.com/Xoshbin)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
