<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect\Tests\Unit;

use Illuminate\Support\Facades\App;
use Xoshbin\TranslatableSelect\Services\LocaleResolver;
use Xoshbin\TranslatableSelect\Services\TranslatableSearchService;
use Xoshbin\TranslatableSelect\Tests\Database\Factories\CategoryFactory;
use Xoshbin\TranslatableSelect\Tests\Models\Category;
use Xoshbin\TranslatableSelect\Tests\TestCase;

class TranslatableSearchServiceTest extends TestCase
{
    private TranslatableSearchService $searchService;

    private LocaleResolver $localeResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->localeResolver = new LocaleResolver;
        $this->searchService = new TranslatableSearchService($this->localeResolver);
    }

    public function test_searches_across_locales(): void
    {
        // Create test categories with specific translations
        CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Technology',
            'ku' => 'تەکنەلۆژیا',
            'ar' => 'تكنولوجيا',
        ])->create();

        CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Sports',
            'ku' => 'وەرزش',
            'ar' => 'رياضة',
        ])->create();

        CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Music',
            'ku' => 'مۆسیقا',
            'ar' => 'موسيقى',
        ])->create();

        // Search for "Technology" in English
        $results = $this->searchService->searchAcrossLocales(
            Category::class,
            'Technology',
            ['name'],
            ['en', 'ku', 'ar']
        );

        $this->assertCount(1, $results);
        $this->assertEquals('Technology', $results->first()->getTranslation('name', 'en'));

        // Search for Kurdish term
        $results = $this->searchService->searchAcrossLocales(
            Category::class,
            'وەرزش',
            ['name'],
            ['en', 'ku', 'ar']
        );

        $this->assertCount(1, $results);
        $this->assertEquals('Sports', $results->first()->getTranslation('name', 'en'));
    }

    public function test_gets_translated_label_current_locale(): void
    {
        App::setLocale('ku');

        $category = CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Technology',
            'ku' => 'تەکنەلۆژیا',
            'ar' => 'تكنولوجيا',
        ])->create();

        $label = $this->searchService->getTranslatedLabel($category, 'name');

        $this->assertEquals('تەکنەلۆژیا', $label);
    }

    public function test_gets_translated_label_fallback_locale(): void
    {
        App::setLocale('fr'); // Not available in translations

        $category = CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Technology',
            'ku' => 'تەکنەلۆژیا',
            'ar' => 'تكنولوجيا',
        ])->create();

        $label = $this->searchService->getTranslatedLabel($category, 'name');

        $this->assertEquals('Technology', $label); // Should fallback to English
    }

    public function test_gets_translated_label_with_preferred_locale(): void
    {
        App::setLocale('en');

        $category = CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Technology',
            'ku' => 'تەکنەلۆژیا',
            'ar' => 'تكنولوجيا',
        ])->create();

        $label = $this->searchService->getTranslatedLabel($category, 'name', 'ar');

        $this->assertEquals('تكنولوجيا', $label);
    }

    public function test_gets_filament_search_results(): void
    {
        CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Technology News',
            'ku' => 'هەواڵی تەکنەلۆژیا',
            'ar' => 'أخبار التكنولوجيا',
        ])->create();

        CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Sports News',
            'ku' => 'هەواڵی وەرزش',
            'ar' => 'أخبار الرياضة',
        ])->create();

        $results = $this->searchService->getFilamentSearchResults(
            Category::class,
            'News',
            [
                'searchFields' => ['name'],
                'labelField' => 'name',
                'searchLocales' => ['en', 'ku', 'ar'],
            ]
        );

        $this->assertCount(2, $results);
        $this->assertArrayHasKey(1, $results);
        $this->assertArrayHasKey(2, $results);
    }

    public function test_gets_translated_labels_for_multiple_models(): void
    {
        $categories = collect([
            CategoryFactory::new()->withSpecificTranslations([
                'en' => 'Technology',
                'ku' => 'تەکنەلۆژیا',
                'ar' => 'تكنولوجيا',
            ])->create(),
            CategoryFactory::new()->withSpecificTranslations([
                'en' => 'Sports',
                'ku' => 'وەرزش',
                'ar' => 'رياضة',
            ])->create(),
        ]);

        App::setLocale('ku');

        $labels = $this->searchService->getTranslatedLabels($categories, 'name');

        $this->assertEquals([
            1 => 'تەکنەلۆژیا',
            2 => 'وەرزش',
        ], $labels);
    }

    public function test_preloads_options(): void
    {
        CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Technology',
            'ku' => 'تەکنەلۆژیا',
            'ar' => 'تكنولوجيا',
        ])->create();

        CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Sports',
            'ku' => 'وەرزش',
            'ar' => 'رياضة',
        ])->create();

        App::setLocale('en');

        $options = $this->searchService->preloadOptions(Category::class, 'name');

        $this->assertEquals([
            1 => 'Technology',
            2 => 'Sports',
        ], $options);
    }

    public function test_searches_with_query_modifier(): void
    {
        CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Active Technology',
            'ku' => 'تەکنەلۆژیای چالاک',
            'ar' => 'تكنولوجيا نشطة',
        ])->active()->create();

        CategoryFactory::new()->withSpecificTranslations([
            'en' => 'Inactive Technology',
            'ku' => 'تەکنەلۆژیای ناچالاک',
            'ar' => 'تكنولوجيا غير نشطة',
        ])->inactive()->create();

        $results = $this->searchService->searchAcrossLocales(
            Category::class,
            'Technology',
            ['name'],
            ['en', 'ku', 'ar'],
            fn ($query) => $query->where('active', true)
        );

        $this->assertCount(1, $results);
        $this->assertEquals('Active Technology', $results->first()->getTranslation('name', 'en'));
    }

    public function test_handles_non_translatable_model(): void
    {
        $nonTranslatableModel = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $table = 'categories';

            protected $fillable = ['name'];
        };

        $label = $this->searchService->getTranslatedLabel($nonTranslatableModel, 'name');

        $this->assertEquals('', $label);
    }
}
