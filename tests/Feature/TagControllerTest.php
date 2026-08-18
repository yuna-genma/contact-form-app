<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_tag_can_show_edit_page(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->get("/admin/tags/{$tag->id}/edit");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_can_create()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/tags', [
            'name' => 'テストタグ',
        ]);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function test_tag_can_update()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => 'テストタグ']);

        $response = $this->actingAs($user)->put("/admin/tags/{$tag->id}", [
            'name' => 'タグ更新',
        ]);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', [
            'name' => 'タグ更新',
        ]);
    }

    /** @test */
    public function test_tag_can_destroy()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => 'テストタグ']);

        $response = $this->actingAs($user)->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function test_guest_access_tags_redirect_login(): void
    {
        $response = $this->post('/admin/tags');

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_tag_name_create_must_be_unique()
    {
        $user = User::factory()->create();
        Tag::factory()->create(['name' => 'テストタグ']);

        $response = $this->actingAs($user)->post('/admin/tags', [
            'name' => 'テストタグ',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_tag_name_update_can_keep_same_name()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => 'テストタグ']);

        $response = $this->actingAs($user)->put("/admin/tags/{$tag->id}", [
            'name' => 'テストタグ',
        ]);

        $response->assertRedirect('/admin');
    }

    /** @test */
    public function test_tag_name_update_must_be_unique()
    {
        $user = User::factory()->create();
        Tag::factory()->create(['name' => '更新失敗']);
        $tag = Tag::factory()->create(['name' => 'テストタグ']);

        $response = $this->actingAs($user)->put("/admin/tags/{$tag->id}", [
            'name' => '更新失敗',
        ]);

        $response->assertSessionHasErrors('name');
    }
}
