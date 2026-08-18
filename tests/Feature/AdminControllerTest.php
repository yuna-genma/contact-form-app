<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_admin_show_can_get(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['content' => 'テストカテゴリ']);

        $contact = Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
        ]);

        $tag = Tag::factory()->create(['name' => 'テストタグ']);
        $contact->tags()->attach($tag);

        $response = $this->actingAs($user)->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertSee('テスト');
        $response->assertSee('太郎');
        $response->assertSee('テストカテゴリ');
        $response->assertSee('テストタグ');
    }

    /** @test */
    public function test_contact_can_destroy_and_redirect_admin()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    /** @test */
    public function test_admin_index_pagenation_7_idems()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->count(8)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $contacts = $response->viewData('contacts');
        $this->assertCount(7, $contacts);
    }

    /** @test */
    public function test_contact_filters_working()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $categoryA = Category::factory()->create(['content' => 'カテゴリA']);
        $categoryB = Category::factory()->create(['content' => 'カテゴリB']);

        $targetContact = Contact::factory()->create([
            'first_name' => '検索対象',
            'last_name' => '太郎',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'created_at' => '2026-01-15 10:00:00',
        ]);

        $dummyContact = Contact::factory()->create([
            'first_name' => '無関係',
            'last_name' => '次郎',
            'gender' => 2,
            'category_id' => $categoryB->id,
            'created_at' => '2026-05-20 10:00:00',
        ]);

        $response = $this->actingAs($user)->get('/admin?'.http_build_query([
            'keyword' => '検索対象',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'date' => '2026-01-15',
        ]));

        $response->assertStatus(200);

        $response->assertSee('検索対象');
        $response->assertDontSee('無関係');
    }
}
