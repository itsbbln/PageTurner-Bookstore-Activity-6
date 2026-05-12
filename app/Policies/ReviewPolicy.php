<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Book $book): bool
    {
        if (! $user->hasVerifiedEmail()) {
            return false;
        }

        return Order::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereHas('orderItems', function ($query) use ($book) {
                $query->where('book_id', $book->id);
            })
            ->exists();
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->isAdmin();
    }
}

