<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;
use App\Models\Tag;
use App\Models\Category;
class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $tags = Tag::all();
        $categoryIds = Category::pluck('id')->toArray();

        Contact::factory()->count(20)->create([
            'category_id' => fn() => fake()->randomElement($categoryIds),
        ])->each(function ($contact) use ($tags) {
            $contact->tags()->attach(
                $tags->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
