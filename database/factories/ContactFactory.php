<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
class ContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->lastName(),
            'last_name' => fake()->firstName(),
            'gender' => fake()->randomElement([1, 2, 3]),
            'email' => fake()->email(),
            'tel' => fake()->numerify('0##########'),
            'address' => fake()->address(),
            'building' => fake()->sentence(),
            'detail' => fake()->text(120),
            'category_id' => Category::factory(),
        ];
    }
}
