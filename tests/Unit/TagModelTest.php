<?php

namespace Tests\Unit;

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
    }
}
