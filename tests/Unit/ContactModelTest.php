<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_contact_belongsto_category(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create(['category_id' => $category->id]);

        $contactCategory = $contact->category;

        $this->assertInstanceOf(Category::class, $contactCategory);
        $this->assertEquals($category->id, $contactCategory->id);
    }

    /** @test */
    public function test_contact_belongstomany_tags()
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create(['category_id' => $category->id]);
        $tags = Tag::factory()->count(3)->create();

        $contact->tags()->sync($tags->pluck('id'));

        $this->assertInstanceOf(Collection::class, $contact->tags);
        $this->assertCount(3, $contact->tags);
        $this->assertInstanceOf(Tag::class, $contact->tags->first());
        $this->assertEquals(
            $tags->pluck('id')->sort()->values()->toArray(),
            $contact->tags->pluck('id')->sort()->values()->toArray()
        );
    }
}
