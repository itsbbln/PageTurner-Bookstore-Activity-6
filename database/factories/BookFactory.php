<?php
namespace Database\Factories;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
        'category_id' => Category::factory(),
        'title' => fake()->sentence(3),
        'author' => fake()->name(),
        'isbn' => fake()->unique()->isbn13(),
        'price' => fake()->randomFloat(2, 9.99, 99.99),
        'stock_quantity' => fake()->numberBetween(0, 100),
        'description' => fake()->paragraphs(3, true),
        'cover_image' => null,
        ];
    }
}