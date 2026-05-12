<?php
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DataPortabilityController;
use App\Http\Controllers\Admin\BookDataController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DataManagementController;
use App\Http\Controllers\Admin\OrderExportController;
use App\Http\Controllers\Admin\UserDataController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Book browsing (public)
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// Category browsing (public)
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class,
'show'])->name('categories.show');

// Shopping cart routes (public)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{book}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{book}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{book}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Authenticated routes (customers)
Route::middleware(['auth', 'verified', 'twofactor'])->group(function () {

    // Customer dashboard
    Route::get('/dashboard', [DashboardController::class, 'customer'])
        ->name('dashboard');

    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Review routes
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');

    // Order routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/export/history', [OrderController::class, 'exportHistory'])->name('orders.export.history');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'downloadInvoice'])->name('orders.invoice');
    Route::get('/my-data/export', [DataPortabilityController::class, 'exportMyData'])->name('data.export.my');
    Route::get('/reading-history/export', [DataPortabilityController::class, 'exportReadingHistory'])->name('data.export.reading-history');
});

// Two-factor authentication routes
Route::middleware(['auth'])->group(function () {
    Route::get('/two-factor/settings', [TwoFactorController::class, 'settings'])
        ->name('two-factor.settings');
    Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])
        ->name('two-factor.enable');
    Route::delete('/two-factor/disable', [TwoFactorController::class, 'disable'])
        ->name('two-factor.disable');

    Route::get('/two-factor/challenge', [TwoFactorController::class, 'showChallenge'])
        ->middleware('throttle:6,1')
        ->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('two-factor.verify');
    Route::post('/two-factor/resend', [TwoFactorController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('two-factor.resend');
});

// Admin-only routes (Category & Book management, Admin dashboard)
Route::middleware(['auth','is_admin','verified','twofactor'])->prefix('admin')->name('admin.')->group(function () {

// Admin dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Admin Data Management hub
Route::get('/data-management', [DataManagementController::class, 'index'])->name('data-management.index');

// Category management
Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

// Book management
Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
Route::post('/books', [BookController::class, 'store'])->name('books.store');
Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

// Order management
Route::get('/orders', [OrderController::class, 'adminIndex'])->name('orders.index');
Route::put('/orders/{order}/status', [OrderController::class, 'update'])->name('orders.update');
Route::post('/orders/export', [OrderExportController::class, 'exportOrders'])->name('orders.export');
Route::post('/orders/export/financial', [OrderExportController::class, 'exportFinancial'])->name('orders.export.financial');

// User management (optional)
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/import-template', [UserDataController::class, 'downloadTemplate'])->name('users.template');
Route::post('/users/import', [UserDataController::class, 'import'])->name('users.import');
Route::post('/users/export', [UserDataController::class, 'export'])->name('users.export');

// Audit logs
Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
Route::get('/audit-logs/{audit}', [AuditLogController::class, 'show'])->name('audit.show');
Route::get('/audit/{audit}/details', [AuditLogController::class, 'details'])->name('audit.details');
Route::get('/audit-logs-export/csv', [AuditLogController::class, 'exportCsv'])->name('audit.export.csv');
Route::get('/audit-logs-export/pdf', [AuditLogController::class, 'exportPdf'])->name('audit.export.pdf');

// Data Management: Book Import/Export
Route::get('/books/data', [BookDataController::class, 'index'])->name('books.data.index');
Route::post('/books/data/import', [BookDataController::class, 'import'])->name('books.data.import');
Route::get('/books/data/import-template', [BookDataController::class, 'downloadTemplate'])->name('books.data.template');
Route::get('/books/data/import-logs/{importLog:uuid}', [BookDataController::class, 'showImportLog'])->name('books.data.import-logs.show');
Route::get('/books/data/import-logs/{importLog:uuid}/failure-report', [BookDataController::class, 'downloadImportFailureReport'])->name('books.data.import-logs.failure-report');
Route::delete('/books/data/import-logs/{importLog:uuid}', [BookDataController::class, 'destroyImportLog'])->name('books.data.import-logs.destroy');

Route::post('/books/data/export', [BookDataController::class, 'export'])->name('books.data.export');
Route::get('/books/data/export-logs/{exportLog:uuid}', [BookDataController::class, 'showExportLog'])->name('books.data.export-logs.show');
Route::get('/books/data/export-logs/{exportLog:uuid}/download', [BookDataController::class, 'downloadExport'])->name('books.data.export-logs.download');
});
// Logout
Route::post('/logout', [ProfileController::class, 'logout'])->name('logout');
require __DIR__.'/auth.php';