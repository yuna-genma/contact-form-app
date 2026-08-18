<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_store_tag_request_validation_passes(): void
    {
        $request = new StoreTagRequest;
        $tag = ['name' => 'テストタグ'];

        $validator = Validator::make($tag, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function test_tag_name_required()
    {
        $request = new StoreTagRequest;
        $tag = ['name' => ''];

        $validator = Validator::make($tag, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /** @test */
    public function test_tag_name_max_50_characters()
    {
        $request = new StoreTagRequest;
        $tag = ['name' => str_repeat('あ', 50)];

        $validator = Validator::make($tag, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function test_tag_name_must_be_within_50()
    {
        $request = new StoreTagRequest;
        $tag = ['name' => str_repeat('あ', 51)];

        $validator = Validator::make($tag, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
