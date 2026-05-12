<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Admin dashboard.
     */
    public function index()
    {
        $totalBooks = Book::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalCustomers = User::where('role', 'customer')->count();

        $recentOrders = Order::with('user')
            ->latest()
            ->take(10)
            ->get();

        $orderStatusSummary = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentReviews = Review::with(['book', 'user'])
            ->latest()
            ->take(10)
            ->get();

        $latestBackup = null;
        $recentTaskFailures = 0;
        $recentRateLimitHits = 0;
        $queueFailedJobs = 0;

        if (Schema::hasTable('backup_monitoring')) {
            $latestBackup = DB::table('backup_monitoring')->latest('executed_at')->first();
        }
        if (Schema::hasTable('scheduled_tasks')) {
            $recentTaskFailures = DB::table('scheduled_tasks')
                ->where('status', 'failed')
                ->where('executed_at', '>=', now()->subDays(7))
                ->count();
        }
        if (Schema::hasTable('api_rate_limits')) {
            $recentRateLimitHits = DB::table('api_rate_limits')
                ->where('hit_at', '>=', now()->subDay())
                ->count();
        }
        if (Schema::hasTable('failed_jobs')) {
            $queueFailedJobs = DB::table('failed_jobs')->count();
        }

        return view('dashboard', compact(
            'totalBooks',
            'totalCategories',
            'totalOrders',
            'completedOrders',
            'pendingOrders',
            'totalCustomers',
            'recentOrders',
            'orderStatusSummary',
            'recentReviews',
            'latestBackup',
            'recentTaskFailures',
            'recentRateLimitHits',
            'queueFailedJobs'
        ));
    }

    /**
     * Customer dashboard.
     */
    public function customer()
    {
        $user = auth()->user();

        $ordersQuery = $user->orders()->with('orderItems.book');

        $totalOrders = (clone $ordersQuery)->count();
        $recentOrders = (clone $ordersQuery)->latest()->take(5)->get();

        $orderStatusSummary = (clone $ordersQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentBooks = $user->orders()
            ->where('status', 'completed')
            ->with('orderItems.book')
            ->latest()
            ->take(10)
            ->get()
            ->flatMap->orderItems
            ->pluck('book')
            ->unique('id')
            ->values();

        $reviews = $user->reviews()
            ->with('book')
            ->latest()
            ->take(10)
            ->get();

        return view('user.dashboard', [
            'user' => $user,
            'totalOrders' => $totalOrders,
            'recentOrders' => $recentOrders,
            'orderStatusSummary' => $orderStatusSummary,
            'recentBooks' => $recentBooks,
            'reviews' => $reviews,
        ]);
    }
}

