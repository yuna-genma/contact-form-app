<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class TagModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_tag_belongstomany_contacts(): void
    {
        $category = Category::factory()->create();
        $contacts = Contact::factory()->count(3)->create([
            'category_id' => $category->id
        ]);
        $tag = Tag::factory()->create();

        $tag->contacts()->sync($contacts->pluck('id'));

        $this->assertInstanceOf(Collection::class, $tag->contacts);
        $this->assertCount(3, $tag->contacts);
        $this->assertInstanceOf(Contact::class, $tag->contacts->first());
        $this->assertEquals(
            $tag->contacts->pluck('id')->sort()->values()->toArray(),
            $contacts->pluck('id')->sort()->values()->toArray()
        );
    }
}
