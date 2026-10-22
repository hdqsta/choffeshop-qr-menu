<?php

use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\TableEntryController;
use App\Livewire\Customer\Cart;
use App\Livewire\Customer\Checkout;
use App\Livewire\Customer\Menu;
use App\Livewire\Customer\OrderStatus;
use App\Livewire\Staff\BarBoard;
use App\Livewire\Staff\CashierBoard;
use App\Livewire\Staff\ModifierManager;
use App\Livewire\Staff\ProductManager;
use App\Livewire\Staff\StaffManager;
use App\Livewire\Staff\TableManager;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// Customer Table Entry (Scan QR)
Route::get('t/{token}', TableEntryController::class)->name('table.entry');

// Customer Order Flow (Protected by Table Session)
Route::middleware('table.session')->group(function (): void {
    Route::get('menu', Menu::class)->name('customer.menu');
    Route::get('cart', Cart::class)->name('customer.cart');
    Route::get('checkout', Checkout::class)->name('customer.checkout');
});

// Customer Order Status (Public Token)
// Parameter route wajib bernama sama dengan argumen mount() komponen Livewire,
// karena Livewire mencocokkan parameter route berdasarkan nama.
Route::get('order/{publicToken}', OrderStatus::class)->name('customer.order.status');

// Staff Dashboard & Profile
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Staff Management Routes (Admin Role)
Route::middleware(['auth', 'role:admin'])->prefix('staff')->name('staff.')->group(function (): void {
    Route::get('products', ProductManager::class)->name('products');
    Route::get('modifiers', ModifierManager::class)->name('modifiers');
    Route::get('tables', TableManager::class)->name('tables');
    Route::get('users', StaffManager::class)->name('users');
});

// Cashier Board (Admin & Cashier Roles)
Route::middleware(['auth', 'role:admin,cashier'])->prefix('staff')->name('staff.')->group(function (): void {
    Route::get('cashier', CashierBoard::class)->name('cashier');
});

// Thermal Receipt Printing — shared by the cashier and the bar station so the
// ticket prints where the drink is made without staff intervention (F7).
Route::middleware(['auth', 'role:admin,cashier,bar'])->prefix('staff')->name('staff.')->group(function (): void {
    Route::get('orders/{order}/receipt', [ReceiptController::class, 'print'])->name('orders.receipt');
});

// Bar Station Board (Admin & Bar Roles)
Route::middleware(['auth', 'role:admin,bar'])->prefix('staff')->name('staff.')->group(function (): void {
    Route::get('bar', BarBoard::class)->name('bar');
});

require __DIR__.'/auth.php';
