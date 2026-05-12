<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\NewOrderCreatedNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Exports\CustomerOrdersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $orders = $user->orders()->with('orderItems.book')->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display all orders (Admin only)
     */
    public function adminIndex(Request $request)
    {
        // Default to showing 5 orders; allow admin to change page size.
        $perPageInput = $request->input('per_page', 5);
        $perPage = $perPageInput === 'all'
            ? max(Order::count(), 5)
            : (int) $perPageInput;

        if ($perPage <= 0) {
            $perPage = 5;
        }

        $orders = Order::with(['user', 'orderItems.book'])
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPageInput]);

        return view('admin.orders.index', [
            'orders' => $orders,
            'perPageInput' => $perPageInput,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Collect customer contact information for this order
        $validatedOrder = $request->validate([
            'contact_number' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:1000',
        ]);

        // Get cart from session
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Cart is empty. Please add items before checkout.');
        }

        $totalAmount = 0;
        $orderItems = [];

        // Validate all books and stock
        foreach ($cart as $bookId => $quantity) {
            $book = Book::findOrFail($bookId);

            if ($book->stock_quantity < $quantity) {
                return back()->with('error', "Insufficient stock for '{$book->title}'. Available: {$book->stock_quantity}");
            }

            $subtotal = $book->price * $quantity;
            $totalAmount += $subtotal;
            $orderItems[$bookId] = [
                'quantity' => $quantity,
                'unit_price' => $book->price,
            ];
        }

        // Create order
        $order = auth()->user()->orders()->create([
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'contact_number' => $validatedOrder['contact_number'],
            'shipping_address' => $validatedOrder['shipping_address'],
        ]);

        // Create order items
        foreach ($orderItems as $bookId => $item) {
            $order->orderItems()->create([
                'book_id' => $bookId,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }
        // deduct stock
        foreach ($cart as $bookId => $quantity) {
            $book = Book::find($bookId);
            $book->decrement('stock_quantity', $quantity);
        }

        // Clear cart after successful order
        session()->forget('cart');

        // Notify customer
        $order->user->notify(new OrderPlacedNotification($order));

        // Notify admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewOrderCreatedNotification($order));
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load('orderItems.book');
        return view('orders.show', compact('order'));
    }

    public function exportHistory(Request $request)
    {
        $user = auth()->user();
        $format = $request->input('format', 'xlsx');
        abort_unless(in_array($format, ['xlsx', 'csv', 'pdf'], true), 422);

        $filename = "my_orders_{$user->id}_" . now()->format('Ymd_His') . ".{$format}";

        if ($format === 'pdf') {
            $orders = $user->orders()->with('orderItems.book')->latest()->get();
            $pdf = Pdf::loadView('orders.history-pdf', ['orders' => $orders, 'user' => $user]);
            return $pdf->download($filename);
        }

        $writer = $format === 'xlsx' ? ExcelFormat::XLSX : ExcelFormat::CSV;
        return Excel::download(new CustomerOrdersExport($user->id), $filename, $writer);
    }

    public function downloadInvoice(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['user', 'orderItems.book']);

        $filename = "invoice_order_{$order->id}.pdf";
        $pdf = Pdf::loadView('orders.invoice-pdf', ['order' => $order]);

        return $pdf->download($filename);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage (admin only).
     */
    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);
        // This method is for admin status updates
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        // If an order is being cancelled (and was not previously cancelled),
        // return the items to stock.
        if ($oldStatus !== 'cancelled' && $newStatus === 'cancelled') {
            $order->load('orderItems.book');

            foreach ($order->orderItems as $item) {
                if ($item->book) {
                    $item->book->increment('stock_quantity', $item->quantity);
                }
            }
        }

        $order->update(['status' => $newStatus]);

        // Notify customer about status change
        $order->user->notify(new OrderStatusUpdatedNotification($order));

        return back()->with('success', 'Order status updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
