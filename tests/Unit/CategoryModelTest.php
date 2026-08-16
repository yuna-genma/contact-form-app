<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Contact;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_category_hasmany_contacts(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create(['category_id' => $category->id]);

        $contacts = $category->contacts;

        $this->assertInstanceOf(Collection::class, $contacts);

        $this->assertTrue($contacts->contains($contact));

        $this->assertCount(1, $contacts);
    }
}
