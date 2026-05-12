<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['Fiction', 'Non-Fiction', 'Science',

        'Technology',

        'Biography', 'History', 'Romance', 'Mystery',
        'Self-Help', 'Children'];

        return [
        'name' => fake()->unique()->randomElement($categories),
        'description' => fake()->paragraph(),
        ];
    }
}