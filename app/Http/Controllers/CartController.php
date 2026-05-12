<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the shopping cart
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        $items = [];

        foreach ($cart as $bookId => $quantity) {
            $book = Book::find($bookId);
            if ($book) {
                $subtotal = $book->price * $quantity;
                $total += $subtotal;
                $items[] = [
                    'book' => $book,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }
        }

        return view('cart.index', compact('items', 'total'));
    }

    /**
     * Add a book to the cart
     */
    public function add(Request $request, Book $book)
    {
        $quantity = $request->input('quantity', 1);

        // Validate quantity
        if ($quantity < 1 || $quantity > $book->stock_quantity) {
            return back()->with('error', 'Invalid quantity. Stock available: ' . $book->stock_quantity);
        }

        // Get current cart from session
        $cart = session()->get('cart', []);

        // Add or update book in cart
        if (isset($cart[$book->id])) {
            $cart[$book->id] += $quantity;
        } else {
            $cart[$book->id] = $quantity;
        }

        // Validate stock for total quantity
        if ($cart[$book->id] > $book->stock_quantity) {
            return back()->with('error', 'Cannot add more. Stock available: ' . $book->stock_quantity);
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')
            ->with('success', 'Book added to cart!');
    }

    /**
     * Update quantity in cart
     */
    public function update(Request $request, Book $book)
    {
        $quantity = $request->input('quantity', 1);

        if ($quantity < 1) {
            return $this->remove($book);
        }

        if ($quantity > $book->stock_quantity) {
            return back()->with('error', 'Quantity exceeds available stock: ' . $book->stock_quantity);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$book->id])) {
            $cart[$book->id] = $quantity;
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Cart updated!');
    }

    /**
     * Remove a book from cart
     */
    public function remove(Book $book)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$book->id])) {
            unset($cart[$book->id]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Book removed from cart!');
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')
            ->with('success', 'Cart cleared!');
    }

    /**
     * Get cart count (for navbar)
     */
    public function count()
    {
        $cart = session()->get('cart', []);
        $count = array_sum($cart);
        return response()->json(['count' => $count]);
    }
}
