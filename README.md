# Filament TranslatableSelect

[![Latest Version on Packagist](https://img.shields.io/packagist/v/xoshbin/translatable-select.svg?style=flat-square)](https://packagist.org/packages/xoshbin/translatable-select)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/xoshbin/translatable-select/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/xoshbin/translatable-select/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/xoshbin/translatable-select/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/xoshbin/translatable-select/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/xoshbin/translatable-select.svg?style=flat-square)](https://packagist.org/packages/xoshbin/translatable-select)

A powerful Filament v4 Select component that **fully extends** Filament's native Select while adding **cross-locale search functionality** for **Spatie Laravel Translatable** models. Search across all locales simultaneously, regardless of the current application locale, while maintaining 100% compatibility with all native Filament Select features.

## 🚀 Key Features

- **🔍 Cross-Locale Search**: Search across all locales simultaneously, regardless of current app locale
- **🎯 Full Filament Compatibility**: Inherits ALL native Select features (relationships, multi-select, preloading, etc.)
- **⚡ Performance Optimized**: Efficient JSON column queries with N+1 prevention
- **🔧 Highly Configurable**: Custom search fields, locales, query modifiers, and more
- **🧪 Thoroughly Tested**: 52 tests covering unit, feature, and integration scenarios
- **🏗️ Clean Architecture**: Separation of concerns with dedicated services
- **📦 Zero Breaking Changes**: Drop-in replacement for standard Filament Select
- **🌐 Database Agnostic**: Optimized for MySQL, PostgreSQL, and SQLite

## 📋 Requirements

- **PHP**: ^8.1
- **Laravel**: ^10.0|^11.0
- **Filament**: ^4.0 (v4 only - clean, modern implementation)
- **Spatie Laravel Translatable**: ^6.0

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

- Default search behavior and limits
- Locale resolution strategies
- Performance optimization settings
- Database-specific configurations

## 🎯 Core Concept: Full Filament Select Compatibility + Cross-Locale Search

`TranslatableSelect` is a **complete extension** of Filament's native Select component. It inherits ALL standard functionality while adding powerful cross-locale search capabilities.

### **What Makes It Special:**

✅ **100% Filament Select Compatibility**: All native features work exactly as expected
✅ **Cross-Locale Search**: Search across all locales simultaneously
✅ **Relationship Support**: Full support for Eloquent relationships
✅ **Multi-Select**: Native multiple selection support
✅ **Preloading**: Efficient option preloading
✅ **Query Modification**: Custom query constraints and filters

### **Two Usage Patterns:**

#### **1. Direct Model Usage (forModel)**
```php
// For direct model selection with cross-locale search
TranslatableSelect::forModel('currency_id', Currency::class, 'name')
    ->label('Currency')
    ->searchableFields(['name', 'code'])
    ->required()
```

#### **2. Relationship Usage (relationship)**
```php
// For Eloquent relationships with cross-locale search
TranslatableSelect::make('category_id')
    ->relationship('category', 'name')
    ->searchableFields(['name', 'description'])
    ->multiple()
    ->preload()
```

### **Key Advantages:**

- **Drop-in Replacement**: Replace any `Select::make()` with `TranslatableSelect::make()`
- **Enhanced Search**: Automatically searches across all locales
- **Performance Optimized**: Efficient JSON column queries
- **Highly Configurable**: Extensive customization options

## 🔧 Basic Usage

### Quick Start Examples

#### **Simple Model Selection**
```php
use Xoshbin\TranslatableSelect\Components\TranslatableSelect;

// Basic usage - searches across all locales automatically
TranslatableSelect::forModel('currency_id', Currency::class, 'name')
    ->label('Currency')
    ->required()
```

#### **Relationship Selection**
```php
// Works with Eloquent relationships
TranslatableSelect::make('category_id')
    ->relationship('category', 'name')
    ->label('Category')
    ->multiple()
    ->preload()
```

#### **Advanced Configuration**
```php
// Full feature example
TranslatableSelect::forModel('product_id', Product::class, 'name')
    ->label('Product')
    ->searchableFields(['name', 'description'])
    ->searchLocales(['en', 'ar', 'ku'])
    ->modifyQueryUsing(fn($query) => $query->where('active', true))
    ->multiple()
    ->preload()
    ->required()
```

## ⚙️ Configuration Options

### Search Configuration

#### **Custom Search Fields**
```php
TranslatableSelect::forModel('product_id', Product::class, 'name')
    ->searchableFields(['name', 'description', 'sku'])  // Search multiple fields
```

#### **Limit Search Locales**
```php
TranslatableSelect::forModel('category_id', Category::class, 'name')
    ->searchLocales(['en', 'ar'])  // Only search in specific locales
```

#### **Fallback Locale**
```php
TranslatableSelect::forModel('tag_id', Tag::class, 'name')
    ->fallbackLocale('en')  // Fallback when translation missing
```

### Query Customization

#### **Query Modification**
```php
TranslatableSelect::forModel('user_id', User::class, 'name')
    ->modifyQueryUsing(fn($query) => $query->where('active', true))
```

#### **Relationship Constraints**
```php
TranslatableSelect::make('category_id')
    ->relationship('category', 'name')
    ->modifyQueryUsing(fn($query) => $query->where('parent_id', null))
```

### Performance Options

#### **Preloading**
```php
TranslatableSelect::forModel('currency_id', Currency::class, 'name')
    ->preload()  // Load all options immediately
```

#### **Search Debounce**
```php
TranslatableSelect::forModel('product_id', Product::class, 'name')
    ->searchDebounce(300)  // Delay search by 300ms
```

## 🏗️ Model Setup

### Basic Model Configuration

Your translatable models should use the `HasTranslations` trait from Spatie Laravel Translatable:

```php
use Spatie\Translatable\HasTranslations;

class Currency extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = ['code', 'name', 'symbol'];
}
```

### Example Models

#### **Category Model**
```php
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = ['name', 'description', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];
}
```

#### **Product Model with Relationships**
```php
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = ['name', 'description', 'sku', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

## 🔧 Configuration File

The package comes with a comprehensive configuration file. Here are the key settings:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Search Behavior
    |--------------------------------------------------------------------------
    */
    'default_search_limit' => 50,
    'default_search_fields' => ['name'],
    'enable_cross_locale_search' => true,

    /*
    |--------------------------------------------------------------------------
    | Locale Configuration
    |--------------------------------------------------------------------------
    */
    'locale_resolution' => [
        'strategy' => 'auto', // 'auto', 'config', 'manual'
        'fallback_locale' => 'en',
        'available_locales' => ['en', 'ar', 'ku'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'enable_query_caching' => true,
        'cache_duration' => 300, // 5 minutes
        'max_search_results' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Optimization
    |--------------------------------------------------------------------------
    */
    'database' => [
        'case_insensitive_search' => true,
        'json_extraction_patterns' => [
            'mysql' => 'LOWER(JSON_UNQUOTE(JSON_EXTRACT(`{field}`, "$.{locale}"))) LIKE ?',
            'pgsql' => 'LOWER(({field}->>\'{locale}\')) LIKE ?',
            'sqlite' => 'LOWER(json_extract(`{field}`, "$.{locale}")) LIKE ?',
        ],
    ],
];
```

## 🎨 Advanced Examples

### E-commerce Product Selection

```php
TranslatableSelect::forModel('product_id', Product::class, 'name')
    ->label('Product')
    ->searchableFields(['name', 'description', 'sku'])
    ->searchLocales(['en', 'ar', 'ku'])
    ->modifyQueryUsing(fn($query) => $query->where('active', true)->with('category'))
    ->getOptionLabelUsing(fn($record) => "{$record->name} ({$record->sku})")
    ->multiple()
    ->preload()
    ->required()
```

### Multi-Tenant Category Selection

```php
TranslatableSelect::make('category_id')
    ->relationship('category', 'name')
    ->searchableFields(['name', 'description'])
    ->modifyQueryUsing(function($query) {
        return $query->where('tenant_id', Filament::getTenant()->id)
                    ->where('active', true);
    })
    ->preload()
    ->required()
```

### Tag Selection with Custom Styling

```php
TranslatableSelect::forModel('tags', Tag::class, 'name')
    ->label('Tags')
    ->searchableFields(['name'])
    ->searchDebounce(300)
    ->multiple()
    ->preload()
    ->getOptionLabelUsing(fn($record) => "🏷️ {$record->name}")
    ->placeholder('Select tags...')
```

## 🔍 How It Works

### Architecture Overview

The package consists of three main components:

1. **TranslatableSelect Component**: Extends Filament's Select with cross-locale search
2. **LocaleResolver Service**: Handles locale detection and management
3. **TranslatableSearchService**: Manages cross-locale search logic

### Cross-Locale Search Process

1. **Locale Detection**: Automatically detects available locales from:
   - Laravel application configuration (`config/app.php`)
   - Model's translatable configuration
   - Manual configuration via `searchLocales()`

2. **Field Analysis**: Identifies translatable fields from model's `$translatable` array

3. **Query Construction**: Builds efficient database queries using JSON extraction:
   ```sql
   -- Example MySQL query for searching "tech" across locales
   SELECT * FROM categories
   WHERE LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.en"))) LIKE '%tech%'
      OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.ar"))) LIKE '%tech%'
      OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.ku"))) LIKE '%tech%'
   LIMIT 50
   ```

4. **Result Processing**: Formats results using current locale with fallback support

### Performance Optimizations

- **Efficient JSON Queries**: Database-specific optimized JSON extraction
- **Query Limits**: Configurable result limits prevent performance issues
- **N+1 Prevention**: Eager loading for relationships
- **Search Debouncing**: Configurable search delays
- **Preloading Support**: Load options immediately when needed

## 🐛 Troubleshooting

### Common Issues

**1. Component not found**
```bash
# Clear cache and rediscover packages
php artisan config:clear
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

**3. Search not working across locales**
```php
// Check if translatable fields are properly configured
TranslatableSelect::forModel('model_id', YourModel::class, 'name')
    ->searchableFields(['name']) // Explicitly set searchable fields
```

**4. Performance issues**
```php
// Optimize with search limits and debouncing
TranslatableSelect::forModel('product_id', Product::class, 'name')
    ->searchDebounce(300)  // Add 300ms delay
    ->modifyQueryUsing(fn($query) => $query->limit(25))
```

### Debug Helpers

```php
// Check available locales
$localeResolver = app(\Xoshbin\TranslatableSelect\Services\LocaleResolver::class);
dd($localeResolver->getAvailableLocales());

// Test search service directly
$searchService = app(\Xoshbin\TranslatableSelect\Services\TranslatableSearchService::class);
dd($searchService->searchAcrossLocales(
    Currency::class,
    'test',
    ['name'],
    ['en', 'ar']
));

// Check current locale
dd(app()->getLocale());
```

## 🧪 Testing

The package includes a comprehensive test suite with 52 tests covering:

- **Unit Tests**: LocaleResolver and TranslatableSearchService
- **Feature Tests**: TranslatableSelect component functionality
- **Integration Tests**: Filament compatibility and real-world usage

```bash
# Run all tests
composer test

# Run tests with coverage
./vendor/bin/pest --coverage

# Run specific test types
./vendor/bin/pest tests/Unit
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Integration
```

## 🚀 What's New in v2.0

This is a **complete rewrite** of the package with:

- ✅ **Filament v4 Only**: Clean, modern implementation
- ✅ **Full Select Compatibility**: Inherits ALL native Filament Select features
- ✅ **Enhanced Performance**: Optimized queries and N+1 prevention
- ✅ **Comprehensive Tests**: 52 tests with full coverage
- ✅ **Clean Architecture**: Separation of concerns with dedicated services
- ✅ **Zero Breaking Changes**: Drop-in replacement for standard Select

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
