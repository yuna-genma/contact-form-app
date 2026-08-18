<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_contact_index_page_can_get(): void
    {
        $category = Category::factory()->create(['content' => 'テストカテゴリ']);
        $tag = Tag::factory()->create(['name' => 'テストタグ']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
        $response->assertViewHas(['categories', 'tags']);
        $response->assertSee('テストカテゴリ');
        $response->assertSee('テストタグ');
    }

    /** @test */
    public function test_contact_confirm_page_can_get()
    {
        $category = Category::factory()->create(['content' => 'テストカテゴリ']);
        $tag = Tag::factory()->create(['name' => 'テストタグ']);

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('テスト');
        $response->assertSee('太郎');
        $response->assertSee('テストカテゴリ');
        $response->assertSee('テストタグ');
    }

    /** @test */
    public function test_contact_can_create()
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
        ];

        $response = $this->withSession(['contact_input' => $data])
            ->post('/contacts');

        $response->assertRedirect('/thanks');
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'tag_id' => $tag->id,
        ]);
    }

    /** @test */
    public function test_thanks_page_can_get()
    {
        $response = $this->get('/thanks');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_first_name_required()
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('first_name');
    }

    /** @test */
    public function test_last_name_required()
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('last_name');
    }

    /** @test */
    public function test_gender_required()
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => '',
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function test_email_required()
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => '',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function test_tel_required()
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('tel');
    }

    /** @test */
    public function test_address_required()
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('address');
    }

    /** @test */
    public function test_detail_required()
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => '',
            'category_id' => $category->id,
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('detail');
    }

    /** @test */
    public function test_category_id_required()
    {
        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '',
            'detail' => 'これはテストです。',
            'category_id' => '',
        ];

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('category_id');
    }
}
