<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class CsvDownloadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_contacts_can_download_csv_desc(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $oldContact = Contact::factory()->create([
            'first_name' => '古い',
            'last_name' => '問い合わせ',
            'category_id' => $category->id,
            'created_at' => now()->subDays(2),
        ]);

        $newContact = Contact::factory()->create([
            'first_name' => '新しい',
            'last_name' => '問い合わせ',
            'category_id' => $category->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/contacts/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csvContent = $response->streamedContent();

        if (str_starts_with($csvContent, "\xEF\xBB\xBF")) {
            $csvContent = substr($csvContent, 3);
        }

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $csvContent);
        rewind($stream);

        $header = fgetcsv($stream);

        $row1 = fgetcsv($stream);

        $this->assertStringContainsString('新しい', implode(',', $row1));

        $row2 = fgetcsv($stream);
        $this->assertStringContainsString('古い', implode(',', $row2));

        fclose($stream);
    }

    /** @test */
    public function test_contacts_can_download_filter_conditions()
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

        $response = $this->actingAs($user)->get('/contacts/export?' . http_build_query([
            'keyword' => '検索対象',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'date' => '2026-01-15',
        ]));

        $response->assertStatus(200);

        $csvContent = $response->streamedContent();

        if (str_starts_with($csvContent, "\xEF\xBB\xBF")) {
            $csvContent = substr($csvContent, 3);
        }

        $this->assertStringContainsString('検索対象', $csvContent);
        $this->assertStringNotContainsString('無関係', $csvContent);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $csvContent);
        rewind($stream);

        $header = fgetcsv($stream);
        $row1 = fgetcsv($stream);

        $this->assertNotFalse($row1, 'CSVにデータ出力されていません');

        $this->assertStringContainsString('検索対象', implode(',', $row1));

        $row2 = fgetcsv($stream);
        $this->assertFalse($row2, 'フィルタで除外されるべきデータがCSVに含まれています');

        fclose($stream);
    }

    /** @test */
    public function test_export_contact_request_passes()
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

        $response = $this->actingAs($user)->get('/contacts/export?' . $queryParams);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_invalid_gender_faild()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $queryParams = http_build_query([
            'keyword' => 'テスト',
            'gender' => 9,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get('/contacts/export?' . $queryParams);

        $response->assertSessionHasErrors('gender');
    }

    /** @test */
    public function test_does_not_exist_category_id_faild()
    {
        $user = User::factory()->create();

        $queryParams = http_build_query([
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => 9999,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get('/contacts/export?' . $queryParams);

        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function test_guest_cannot_download_csv(): void
    {
        $response = $this->get('/contacts/export');

        $response->assertRedirect(route('login'));
    }
}
