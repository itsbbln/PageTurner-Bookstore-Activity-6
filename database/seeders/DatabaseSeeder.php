<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure admin user (from AdminUserSeeder)
        $this->call(AdminUserSeeder::class);
        $this->call(Lab6Seeder::class);

        // Create customer users
        $customers = User::factory(10)->create([
            'role' => 'customer'
        ]);

        // Create categories
        $categories = Category::factory(8)->create();

        // Create 5 books for each category
        $categories->each(function ($category) {
            Book::factory(5)->create([
                'category_id' => $category->id
            ]);
        });

        // Create reviews
        $books = Book::all();

        $customers->each(function ($customer) use ($books) {

            // Each customer reviews 3–5 random books
            $books->random(rand(3, 5))->each(function ($book) use ($customer) {
                Review::create([
                    'user_id' => $customer->id,
                    'book_id' => $book->id,
                    'rating' => fake()->numberBetween(1, 5),
                    'comment' => fake()->paragraph(),
                ]);
            });

        });
    }
}
