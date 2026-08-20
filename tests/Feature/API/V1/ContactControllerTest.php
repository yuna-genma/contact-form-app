<?php

namespace Tests\Feature\API\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Contact;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_api_index_can_get_json_format(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->count(3)->create([
            'category_id' => $category->id
        ]);

        $contact->each(function ($contact) use ($tag) {
            $contact->tags()->attach($tag);
        });

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function test_api_index_pagination_20_idems()
    {
        $category = Category::factory()->create();

        Contact::factory()->count(25)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $response->assertJsonCount(20, 'data');
        $response->assertJsonPath('meta.total', 25);
        $response->assertJsonPath('meta.per_page', 20);
    }

    /** @test */
    public function test_api_index_filters_working()
    {
        $this->withoutExceptionHandling();

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

        $response = $this->getJson('/api/v1/contacts?' . http_build_query([
            'keyword' => '検索対象',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'date' => '2026-01-15',
        ]));

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $targetContact->id,
            'first_name' => '検索対象',
        ]);
        $response->assertJsonMissing([
            'id' => $dummyContact->id
        ]);
    }

    /** @test */
    public function test_api_show_can_get_json_format()
    {
        $category = Category::factory()->create(['content' => 'テストカテゴリ']);
        $tag = Tag::factory()->create(['name' => 'テストタグ']);
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

        $contact->tags()->attach($tag);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'gender',
                'gender_label',
                'email',
                'tel',
                'address',
                'detail',
                'category' => [
                    'id',
                    'content',
                ],
                'tags' => [
                    '*' => [
                        'id',
                        'name'
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function test_api_show_json_response_corrected()
    {
        $category = Category::factory()->create(['content' => 'テストカテゴリ']);
        $tag = Tag::factory()->create(['name' => 'テストタグ']);
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

        $contact->tags()->attach($tag->id);
        $contact->load('tags');

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'first_name' => 'テスト',
                'last_name' => '太郎',
                'gender' => 1,
                'email' => 'test@example.com',
                'tel' => '09000000000',
                'address' => '東京都',
                'detail' => 'これはテストです。',
                'category' => [
                    'id' => $category->id,
                    'content' => 'テストカテゴリ',
                ],
                'tags' => [
                    [
                        'id' => $tag->id,
                        'name' => 'テストタグ',
                    ]
                ],
            ],
        ]);
    }

    /** @test */
    public function test_api_show_does_not_exist_id_faild()
    {
        $response = $this->getJson('/api/v1/contacts/99999');

        $response->assertNotFound();
    }

    /** @test */
    public function test_api_contact_can_store()
    {
        $category = Category::factory()->create(['content' => 'テストカテゴリ']);
        $tag = Tag::factory()->create(['name' => 'テストタグ']);

        $requestData = [
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

        $response = $this->postJson('/api/v1/contacts', $requestData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'first_name' => 'テスト',
                'last_name' => '太郎',
                'gender' => 1,
                'email' => 'test@example.com',
                'tel' => '09000000000',
                'address' => '東京都',
                'detail' => 'これはテストです。',
                'category' => [
                    'id' => $category->id,
                    'content' => 'テストカテゴリ',
                ],
                'tags' => [
                    [
                        'id' => $tag->id,
                        'name' => 'テストタグ',
                    ]
                ],
            ],
        ]);

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'テスト',
            'last_name' => '太郎',
        ]);
    }

    /** @test */
    public function test_api_store_return_422_when_validation_fails()
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'first_name' => '',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $category->id,
        ]);

        $response = $this->postJson('/api/v1/contacts');

        $response->assertStatus(422);
    }

    /** @test */
    public function test_api_contact_can_update()
    {
        $categoryA = Category::factory()->create(['content' => 'テストカテゴリ']);
        $tagA = Tag::factory()->create(['name' => 'テストタグ']);

        $contact = Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => 'これはテストです。',
            'category_id' => $categoryA->id,
        ]);

        $contact->tags()->attach($tagA->id);

        $categoryB = Category::factory()->create(['content' => '更新後カテゴリ']);
        $tagB = Tag::factory()->create(['name' => '更新後タグ']);

        $updateData = [
            'first_name' => '更新',
            'last_name' => 'しました',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '09099999999',
            'address' => '大阪府',
            'detail' => '更新しました。',
            'category_id' => $categoryB->id,
            'tag_ids' => [$tagB->id],
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'first_name' => '更新',
                'last_name' => 'しました',
                'gender' => 2,
                'email' => 'update@example.com',
                'tel' => '09099999999',
                'address' => '大阪府',
                'detail' => '更新しました。',
                'category' => [
                    'id' => $categoryB->id,
                    'content' => '更新後カテゴリ',
                ],
                'tags' => [
                    [
                        'id' => $tagB->id,
                        'name' => '更新後タグ',
                    ]
                ],
            ],
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '更新',
            'last_name' => 'しました',
            'category_id' => $categoryB->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tagB->id
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tagA->id
        ]);
    }

    /** @test */
    public function test_api_update_does_not_exist_id_faild()
    {
        $response = $this->putJson('/api/v1/contacts/99999');

        $response->assertNotFound();
    }

    /** @test */
    public function test_api_update_first_name_required()
    {
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

        $updateData = [
            'first_name' => '',
            'last_name' => 'しました',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '09099999999',
            'address' => '大阪府',
            'detail' => '更新しました。',
            'category_id' => $category->id,
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_api_update_last_name_required()
    {
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

        $updateData = [
            'first_name' => '更新',
            'last_name' => '',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '09099999999',
            'address' => '大阪府',
            'detail' => '更新しました。',
            'category_id' => $category->id,
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_api_update_gender_required()
    {
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

        $updateData = [
            'first_name' => '更新',
            'last_name' => 'しました',
            'gender' => '',
            'email' => 'update@example.com',
            'tel' => '09099999999',
            'address' => '大阪府',
            'detail' => '更新しました。',
            'category_id' => $category->id,
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_api_update_email_required()
    {
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

        $updateData = [
            'first_name' => '更新',
            'last_name' => 'しました',
            'gender' => 2,
            'email' => '',
            'tel' => '09099999999',
            'address' => '大阪府',
            'detail' => '更新しました。',
            'category_id' => $category->id,
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_api_update_tel_required()
    {
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

        $updateData = [
            'first_name' => '更新',
            'last_name' => 'しました',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '',
            'address' => '大阪府',
            'detail' => '更新しました。',
            'category_id' => $category->id,
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_api_update_address_required()
    {
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

        $updateData = [
            'first_name' => '更新',
            'last_name' => 'しました',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '09099999999',
            'address' => '',
            'detail' => '更新しました。',
            'category_id' => $category->id,
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_api_update_detail_required()
    {
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

        $updateData = [
            'first_name' => '更新',
            'last_name' => 'しました',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '09099999999',
            'address' => '大阪府',
            'detail' => '',
            'category_id' => $category->id,
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_api_update_category_id_required()
    {
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

        $updateData = [
            'first_name' => '更新',
            'last_name' => 'しました',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '09099999999',
            'address' => '大阪府',
            'detail' => '更新しました。',
            'category_id' => '',
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_api_contact_can_destroy()
    {
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

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id
        ]);
    }

    /** @test */
    public function test_api_delete_does_not_exist_id_faild()
    {
        $response = $this->deleteJson('/api/v1/contacts/99999');

        $response->assertNotFound();
    }
}
