<?php

declare(strict_types=1);

namespace Xoshbin\TranslatableSelect\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Xoshbin\TranslatableSelect\Components\TranslatableSelect;
use Xoshbin\TranslatableSelect\Tests\Models\Category;
use Xoshbin\TranslatableSelect\Tests\Models\Product;
use Xoshbin\TranslatableSelect\Tests\Models\Tag;
use Xoshbin\TranslatableSelect\Tests\TestCase;

class ModalRelationshipTest extends TestCase
{
    public function test_it_resolves_relationship_even_if_record_is_from_wrong_model(): void
    {
        // 1. Create a Tag (which doesn't have 'category' relationship)
        $tag = new Tag;

        // 2. Create a Product (which DOES have 'category' relationship)
        $product = new Product;

        // 3. Create a TranslatableSelect with 'category' relationship
        // We subclass it to mock the Filament container/model resolution logic
        $select = new class('category_id') extends TranslatableSelect
        {
            public ?Model $mockRecord = null;

            public ?Model $mockModelInstance = null;

            public function getRecord(bool $withContainerRecord = true): Model | array | null
            {
                return $this->mockRecord;
            }

            public function getModelInstance(): ?Model
            {
                return $this->mockModelInstance;
            }

            // Expose the protected method for testing
            public function callGetRelatedModelClass(): ?string
            {
                return $this->getRelatedModelClass();
            }
        };

        $select->relationship('category', 'name');

        // Set the "wrong" record (simulating being inside a modal opened from RFQ)
        $select->mockRecord = $tag;

        // Set the "right" model instance (simulating the schema model set by Filament in the modal)
        $select->mockModelInstance = $product;

        // Verify it resolves the correct model class instead of throwing BadMethodCallException
        $relatedModelClass = $select->callGetRelatedModelClass();

        $this->assertEquals(Category::class, $relatedModelClass);
    }

    public function test_it_returns_null_if_relationship_exists_on_neither_record_nor_model_instance(): void
    {
        $tag = new Tag;

        $select = new class('non_existent_id') extends TranslatableSelect
        {
            public ?Model $mockRecord = null;

            public function getRecord(bool $withContainerRecord = true): Model | array | null
            {
                return $this->mockRecord;
            }

            public function getModelInstance(): ?Model
            {
                return null;
            }

            public function callGetRelatedModelClass(): ?string
            {
                return $this->getRelatedModelClass();
            }
        };

        $select->relationship('non_existent_relation', 'name');
        $select->mockRecord = $tag;

        $this->assertNull($select->callGetRelatedModelClass());
    }
}
