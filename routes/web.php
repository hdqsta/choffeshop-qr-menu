<?php

use App\Livewire\Staff\ModifierManager;
use App\Livewire\Staff\ProductManager;
use App\Livewire\Staff\TableManager;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

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
});

require __DIR__.'/auth.php';
