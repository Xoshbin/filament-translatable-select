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
- **🔤 Case-Insensitive Search**: Automatic case-insensitive search across all database engines
- **🏢 Multi-Tenancy Ready**: Seamless integration with Laravel Filament's tenancy system

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

## 🔄 Migration from Standard Filament Select

### Simple Drop-in Replacement

TranslatableSelect is designed as a complete drop-in replacement for Filament's native Select component:

```php
// Before: Standard Filament Select
Select::make('category_id')
    ->relationship('category', 'name')
    ->searchable()
    ->preload()

// After: TranslatableSelect with cross-locale search
TranslatableSelect::make('category_id')
    ->relationship('category', 'name')
    ->searchableFields(['name']) // Now searches across all locales!
    ->preload()
```

### Enhanced Features

Add powerful search capabilities to existing selects:

```php
// Standard select with limited search
Select::make('product_id')
    ->relationship('product', 'name')
    ->searchable()

// Enhanced with cross-locale search and multiple fields
TranslatableSelect::make('product_id')
    ->relationship('product', 'name')
    ->searchableFields(['name', 'sku', 'description']) // Search multiple fields
    ->searchLocales(['en', 'ar', 'ku']) // Across multiple locales
    ->searchDebounce(300) // Performance optimization
```

### Migration Checklist

1. **Replace Import Statement**:
   ```php
   // Old
   use Filament\Forms\Components\Select;

   // New
   use Xoshbin\TranslatableSelect\Components\TranslatableSelect;
   ```

2. **Update Component Usage**:
   ```php
   // Old
   Select::make('field_name')

   // New
   TranslatableSelect::make('field_name')
   ```

3. **Add Search Configuration**:
   ```php
   // Add these methods for enhanced functionality
   ->searchableFields(['name', 'code'])
   ->searchLocales(['en', 'ar', 'ku']) // Optional: specify locales
   ```

4. **Review Query Modifiers** (Important for Multi-Tenant Apps):
   ```php
   // Remove manual tenant filtering - let Filament handle it
   // Old (problematic)
   ->modifyQueryUsing(fn($query) => $query->where('company_id', $tenant->id))

   // New (correct)
   ->modifyQueryUsing(fn($query) => $query->where('active', true))
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
            'mysql' => 'LOWER(JSON_UNQUOTE(JSON_EXTRACT(`{field}`, "$.{locale}"))) LIKE LOWER(?)',
            'pgsql' => 'LOWER(({field}->>\'{locale}\')) ILIKE ?',
            'sqlite' => 'LOWER(json_extract(`{field}`, "$.{locale}")) LIKE LOWER(?)',
        ],
    ],
];
```

## 🏢 Multi-Tenancy Considerations

### Overview

TranslatableSelect seamlessly integrates with Laravel Filament's tenancy system. The component automatically respects tenant scoping when used in tenant-aware resources, but there are important considerations to ensure optimal performance and avoid conflicts.

### ⚠️ Important: Avoid Duplicate Company/Tenant Filtering

**❌ INCORRECT - Causes Search Conflicts:**
```php
// DON'T DO THIS - Manual tenant filtering conflicts with Filament's automatic tenancy
TranslatableSelect::forModel('account_id', Account::class, 'name')
    ->modifyQueryUsing(fn($query) => $query->where('company_id', Filament::getTenant()->id))
    ->searchableFields(['name', 'code'])
```

**✅ CORRECT - Let Filament Handle Tenancy:**
```php
// DO THIS - Filament automatically applies tenant scoping
TranslatableSelect::forModel('account_id', Account::class, 'name')
    ->searchableFields(['name', 'code'])
    ->modifyQueryUsing(fn($query) => $query->where('active', true)) // Only add business logic filters
```

### Best Practices for Multi-Tenant Applications

#### **1. Trust Filament's Automatic Tenancy**
```php
// Filament automatically adds tenant scoping - no manual filtering needed
TranslatableSelect::make('category_id')
    ->relationship('category', 'name')
    ->searchableFields(['name', 'description'])
    ->modifyQueryUsing(fn($query) => $query->where('active', true)) // Business logic only
```

#### **2. Use Query Modifiers for Business Logic Only**
```php
// Focus on business rules, not tenant filtering
TranslatableSelect::forModel('product_id', Product::class, 'name')
    ->searchableFields(['name', 'sku'])
    ->modifyQueryUsing(function($query) {
        return $query->where('active', true)
                    ->where('stock_quantity', '>', 0)
                    ->orderBy('name');
    })
```

#### **3. Relationship-Based Tenant Scoping**
```php
// For complex tenant relationships
TranslatableSelect::make('supplier_id')
    ->relationship('supplier', 'name')
    ->searchableFields(['name', 'company_name'])
    ->modifyQueryUsing(fn($query) => $query->where('approved', true))
```

### Multi-Tenant Model Setup

Ensure your models are properly configured for tenancy:

```php
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = ['name', 'code', 'type', 'company_id'];

    // Filament will automatically scope by company_id when using tenancy
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
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

### Multi-Tenant Category Selection (Correct Way)

```php
TranslatableSelect::make('category_id')
    ->relationship('category', 'name')
    ->searchableFields(['name', 'description'])
    ->modifyQueryUsing(fn($query) => $query->where('active', true)) // No manual tenant filtering!
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

### Real-World Usage Examples

#### **Accounting System - Account Selection**
```php
// Income accounts for product configuration
TranslatableSelect::forModel('income_account_id', Account::class, 'name')
    ->label('Income Account')
    ->searchableFields(['name', 'code'])
    ->modifyQueryUsing(fn($query) => $query->whereIn('type', [
        AccountType::Income,
        AccountType::OtherIncome
    ]))
    ->getOptionLabelUsing(fn($record) => "{$record->code} - {$record->name}")
    ->required()

// Expense accounts for product configuration
TranslatableSelect::forModel('expense_account_id', Account::class, 'name')
    ->label('Expense Account')
    ->searchableFields(['name', 'code'])
    ->modifyQueryUsing(fn($query) => $query->whereIn('type', [
        AccountType::Expense,
        AccountType::CostOfGoodsSold
    ]))
    ->getOptionLabelUsing(fn($record) => "{$record->code} - {$record->name}")
    ->required()
```

#### **Inventory Management - Product Selection**
```php
TranslatableSelect::make('product_id')
    ->relationship('product', 'name')
    ->searchableFields(['name', 'sku', 'description'])
    ->modifyQueryUsing(fn($query) => $query->where('active', true))
    ->getOptionLabelUsing(fn($record) => "{$record->sku} - {$record->name}")
    ->preload()
    ->required()
```

#### **CRM System - Customer Selection**
```php
TranslatableSelect::forModel('customer_id', Customer::class, 'name')
    ->label('Customer')
    ->searchableFields(['name', 'company_name', 'email'])
    ->modifyQueryUsing(fn($query) => $query->where('active', true))
    ->getOptionLabelUsing(fn($record) => $record->company_name
        ? "{$record->name} ({$record->company_name})"
        : $record->name)
    ->searchDebounce(300)
    ->required()
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
   -- Example MySQL query for searching "tech" across locales (case-insensitive)
   SELECT * FROM categories
   WHERE LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.en"))) LIKE LOWER('%tech%')
      OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.ar"))) LIKE LOWER('%tech%')
      OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.ku"))) LIKE LOWER('%tech%')
   LIMIT 50
   ```

4. **Result Processing**: Formats results using current locale with fallback support

### Case-Insensitive Search

The component automatically performs case-insensitive searches across all supported database engines:

- **MySQL**: Uses `LOWER()` functions for both JSON fields and search terms
- **PostgreSQL**: Uses `ILIKE` operator for case-insensitive pattern matching
- **SQLite**: Uses `LOWER()` functions similar to MySQL

This means searching for "product", "Product", or "PRODUCT" will all return the same results.

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

**2. "No options match your search" despite valid data**

This is often caused by conflicting query modifiers in multi-tenant applications:

```php
// ❌ PROBLEM: Manual tenant filtering conflicts with Filament's automatic tenancy
TranslatableSelect::forModel('account_id', Account::class, 'name')
    ->modifyQueryUsing(fn($query) => $query->where('company_id', $tenant->id)) // Causes conflicts!

// ✅ SOLUTION: Remove manual tenant filtering, let Filament handle it
TranslatableSelect::forModel('account_id', Account::class, 'name')
    ->searchableFields(['name', 'code'])
    ->modifyQueryUsing(fn($query) => $query->where('active', true)) // Business logic only
```

**3. Case sensitivity issues**

The component automatically handles case-insensitive search, but if you're experiencing issues:

```php
// Ensure you're not overriding the search behavior
TranslatableSelect::forModel('product_id', Product::class, 'name')
    ->searchableFields(['name']) // Let the component handle case sensitivity
```

**4. No search results**
```php
// Ensure your model has the HasTranslations trait
use Spatie\Translatable\HasTranslations;

class YourModel extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];
}
```

**5. Search not working across locales**
```php
// Check if translatable fields are properly configured
TranslatableSelect::forModel('model_id', YourModel::class, 'name')
    ->searchableFields(['name']) // Explicitly set searchable fields
    ->searchLocales(['en', 'ar', 'ku']) // Specify locales if needed
```

**6. Performance issues**
```php
// Optimize with search limits and debouncing
TranslatableSelect::forModel('product_id', Product::class, 'name')
    ->searchDebounce(300)  // Add 300ms delay
    ->modifyQueryUsing(fn($query) => $query->limit(25))
```

**7. Relationship search not working**
```php
// Ensure the relationship method exists and is properly defined
TranslatableSelect::make('category_id')
    ->relationship('category', 'name') // 'category' method must exist on the model
    ->searchableFields(['name'])
```

### Debug Helpers

```php
// Check available locales
$localeResolver = app(\Xoshbin\TranslatableSelect\Services\LocaleResolver::class);
dd($localeResolver->getAvailableLocales());

// Test search service directly
$searchService = app(\Xoshbin\TranslatableSelect\Services\TranslatableSearchService::class);
dd($searchService->getFilamentSearchResults(
    Account::class,
    'product', // Search term
    ['name', 'code'], // Search fields
    ['en', 'ar', 'ku'], // Search locales
    null, // Query modifier
    50 // Limit
));

// Check current locale
dd(app()->getLocale());

// Test model translatable configuration
dd(Account::make()->getTranslatableAttributes());

// Check if tenancy is affecting queries (in tenant-aware resources)
dd(Filament::getTenant()?->getKey());
```

### Debugging Search Issues

If search is not working, enable query logging to see what's happening:

```php
// Add this to your resource or form to debug queries
use Illuminate\Support\Facades\DB;

// Enable query logging
DB::enableQueryLog();

// Perform your search...

// Check the queries
dd(DB::getQueryLog());
```

### Common Query Conflicts

**Problem**: Duplicate tenant filtering
```sql
-- This query shows duplicate company_id conditions
SELECT * FROM accounts
WHERE company_id = 1 -- Filament's automatic tenancy
  AND company_id = 1 -- Your manual filtering (duplicate!)
  AND (LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, "$.en"))) LIKE LOWER('%product%'))
```

**Solution**: Remove manual tenant filtering
```php
// Let Filament handle tenancy automatically
TranslatableSelect::forModel('account_id', Account::class, 'name')
    ->searchableFields(['name', 'code'])
    // No manual company_id filtering needed!
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
- ✅ **Case-Insensitive Search**: Automatic case-insensitive search across all database engines
- ✅ **Multi-Tenancy Integration**: Seamless compatibility with Filament's tenancy system
- ✅ **Improved Error Handling**: Better debugging and troubleshooting capabilities

### Recent Improvements (Latest Release)

#### 🔧 **Fixed Search Functionality Issues**
- **Case Sensitivity**: Resolved case-sensitive search problems where "product" wouldn't match "Product Sales"
- **Multi-Tenancy Conflicts**: Fixed conflicts between manual tenant filtering and Filament's automatic tenancy system
- **Query Optimization**: Improved database query generation for better performance

#### 🏢 **Enhanced Multi-Tenancy Support**
- **Automatic Tenant Scoping**: Works seamlessly with Filament's tenant-aware resources
- **Conflict Prevention**: Prevents duplicate company/tenant filtering that caused search failures
- **Best Practices Documentation**: Comprehensive guide for multi-tenant applications

#### 🔍 **Improved Search Capabilities**
- **Cross-Locale Search**: Search "sales" and find both "Product Sales" and "Sales Discounts & Returns"
- **Case-Insensitive**: Search "product" and match "Product Sales" regardless of case
- **Database Agnostic**: Optimized queries for MySQL, PostgreSQL, and SQLite

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
