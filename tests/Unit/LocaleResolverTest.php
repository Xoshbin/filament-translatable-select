<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect\Tests\Unit;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Xoshbin\TranslatableSelect\Services\LocaleResolver;
use Xoshbin\TranslatableSelect\Tests\Models\Category;
use Xoshbin\TranslatableSelect\Tests\Models\Product;
use Xoshbin\TranslatableSelect\Tests\TestCase;

class LocaleResolverTest extends TestCase
{
    private LocaleResolver $localeResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->localeResolver = new LocaleResolver();
    }

    public function test_gets_current_locale(): void
    {
        App::setLocale('ku');
        
        $this->assertEquals('ku', $this->localeResolver->getCurrentLocale());
    }

    public function test_gets_fallback_locale(): void
    {
        Config::set('app.fallback_locale', 'en');
        
        $this->assertEquals('en', $this->localeResolver->getFallbackLocale());
    }

    public function test_gets_available_locales_from_config(): void
    {
        Config::set('translatable-select.available_locales', ['en', 'ku', 'ar', 'fr']);
        
        $locales = $this->localeResolver->getAvailableLocales();
        
        $this->assertEquals(['en', 'ku', 'ar', 'fr'], $locales);
    }

    public function test_gets_available_locales_fallback(): void
    {
        Config::set('translatable-select.available_locales', null);
        Config::set('translatable.locales', ['en', 'ku']);
        
        $locales = $this->localeResolver->getAvailableLocales();
        
        $this->assertEquals(['en', 'ku'], $locales);
    }

    public function test_gets_model_locales_for_translatable_model(): void
    {
        $locales = $this->localeResolver->getModelLocales(Category::class);
        
        $this->assertEquals(['en', 'ku', 'ar'], $locales);
    }

    public function test_gets_model_locales_for_non_translatable_model(): void
    {
        $nonTranslatableModel = new class extends \Illuminate\Database\Eloquent\Model {};
        
        $locales = $this->localeResolver->getModelLocales($nonTranslatableModel);
        
        $this->assertEquals(['en'], $locales);
    }

    public function test_gets_translatable_attributes(): void
    {
        $attributes = $this->localeResolver->getTranslatableAttributes(Category::class);
        
        $this->assertEquals(['name', 'description'], $attributes);
    }

    public function test_gets_empty_translatable_attributes_for_non_translatable(): void
    {
        $nonTranslatableModel = new class extends \Illuminate\Database\Eloquent\Model {};
        
        $attributes = $this->localeResolver->getTranslatableAttributes($nonTranslatableModel);
        
        $this->assertEquals([], $attributes);
    }

    public function test_checks_if_model_is_translatable(): void
    {
        $this->assertTrue($this->localeResolver->isTranslatable(Category::class));
        $this->assertTrue($this->localeResolver->isTranslatable(Product::class));
        
        $nonTranslatableModel = new class extends \Illuminate\Database\Eloquent\Model {};
        $this->assertFalse($this->localeResolver->isTranslatable($nonTranslatableModel));
    }

    public function test_gets_best_locale_for_display_current_locale(): void
    {
        App::setLocale('ku');
        
        $translations = [
            'en' => 'English Text',
            'ku' => 'Kurdish Text',
            'ar' => 'Arabic Text',
        ];
        
        $bestLocale = $this->localeResolver->getBestLocaleForDisplay($translations);
        
        $this->assertEquals('ku', $bestLocale);
    }

    public function test_gets_best_locale_for_display_fallback_locale(): void
    {
        App::setLocale('fr'); // Not available in translations
        Config::set('app.fallback_locale', 'en');
        
        $translations = [
            'en' => 'English Text',
            'ku' => 'Kurdish Text',
            'ar' => 'Arabic Text',
        ];
        
        $bestLocale = $this->localeResolver->getBestLocaleForDisplay($translations);
        
        $this->assertEquals('en', $bestLocale);
    }

    public function test_gets_best_locale_for_display_first_available(): void
    {
        App::setLocale('fr'); // Not available
        Config::set('app.fallback_locale', 'de'); // Not available
        
        $translations = [
            'ku' => 'Kurdish Text',
            'ar' => 'Arabic Text',
        ];
        
        $bestLocale = $this->localeResolver->getBestLocaleForDisplay($translations);
        
        $this->assertEquals('ku', $bestLocale);
    }

    public function test_gets_best_locale_for_display_with_empty_values(): void
    {
        App::setLocale('ku');
        
        $translations = [
            'en' => 'English Text',
            'ku' => '', // Empty
            'ar' => 'Arabic Text',
        ];
        
        $bestLocale = $this->localeResolver->getBestLocaleForDisplay($translations);
        
        $this->assertEquals('en', $bestLocale); // Should fallback to en
    }

    public function test_resolves_search_locales_with_custom_locales(): void
    {
        $customLocales = ['en', 'fr'];
        
        $locales = $this->localeResolver->resolveSearchLocales(Category::class, $customLocales);
        
        $this->assertEquals(['en', 'fr'], $locales);
    }

    public function test_resolves_search_locales_without_custom_locales(): void
    {
        $locales = $this->localeResolver->resolveSearchLocales(Category::class);
        
        $this->assertEquals(['en', 'ku', 'ar'], $locales);
    }
}
