<?php

namespace Tests\Unit\API\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\API\V1\StoreContactRequest;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Tag;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_api_store_contact_request_validation_passes(): void
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function test_validation_fails_first_name_required()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => '',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('first_name', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_last_name_required()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_gender_required()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => '',
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_invalid_gender()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 9999,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_email_required()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => '',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_invalid_email()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_tel_required()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_invalid_tel()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '090-00000-000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_address_required()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('address', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_category_required()
    {
        $request = new StoreContactRequest;

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => '',
            'detail' => 'これはテストです。',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    /** @test */
    public function test_validation_fails_detail_required()
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => '',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('detail', $validator->errors()->toArray());
    }

    /** @test */
    public function test_tag_addition_validation_passes(): void
    {
        $request = new StoreContactRequest;
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'これはテストです。',
            'tag_ids' => $tag->pluck('id')->toArray(),
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }
}
