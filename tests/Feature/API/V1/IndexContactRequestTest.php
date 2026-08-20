<?php

namespace Tests\Feature\API\V1;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_index_contact_request_validation_passes(): void
    {
        $category = Category::factory()->create();
        Contact::factory()->create([
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'category_id' => $category->id,
            'created_at' => now(),
        ]);

        $queryParams = http_build_query([
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->getJson('/api/v1/contacts?' . $queryParams);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_invalid_gender_error()
    {
        $category = Category::factory()->create();

        $queryParams = http_build_query([
            'keyword' => 'テスト',
            'gender' => 9,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->getJson('/api/v1/contacts?' . $queryParams);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_does_not_exist_keyword_error()
    {
        $category = Category::factory()->create();

        $queryParams = http_build_query([
            'keyword' => str_repeat('あ', 256),
            'gender' => 1,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->getJson('/api/v1/contacts?' . $queryParams);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_does_not_exist_category_id_error()
    {

        $queryParams = http_build_query([
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => 9999,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->getJson('/api/v1/contacts?' . $queryParams);

        $response->assertStatus(422);
    }
}
