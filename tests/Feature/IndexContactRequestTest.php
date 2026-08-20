<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_index_contact_request_validation_passes(): void
    {
        $user = User::factory()->create();
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

        $response = $this->actingAs($user)->get('/admin?' . $queryParams);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_invalid_gender_error()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $queryParams = http_build_query([
            'keyword' => 'テスト',
            'gender' => 9,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->from('/admin')
            ->get('/admin?' . $queryParams);

        $response->assertRedirect('/admin');
        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function test_does_not_exist_keyword_error()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $queryParams = http_build_query([
            'keyword' => str_repeat('あ', 256),
            'gender' => 1,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->from('/admin')
            ->get('/admin?' . $queryParams);

        $response->assertRedirect('/admin');
        $response->assertSessionHasErrors('keyword');
    }

    /** @test */
    public function test_does_not_exist_category_id_error()
    {
        $user = User::factory()->create();

        $queryParams = http_build_query([
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => 9999,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->from('/admin')
            ->get('/admin?' . $queryParams);

        $response->assertRedirect('/admin');
        $response->assertSessionHasErrors('category_id');
    }
}
