<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\Orders\CreateOrderLivewire;
use App\Models\Place;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');

Route::get('/home', function () {
    return view('welcome');
})->middleware('auth')->name('home');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('product')->group(function () {
        Route::get('/index', function () {return view('admin.products.index');})->name('products.index');
        Route::get('/categories', function () {return view('admin.categories.index');})->name('categories.index');
        Route::get('/rooms', function () {return view('admin.places.index');})->name('places.index');
        Route::get('/orders', function () {return view('admin.orders.index');})->name('orders.index');
        Route::get('/cafe', function () {return view('admin.orders.cafe');})->name('cafe.create');
    });
});

require __DIR__.'/auth.php';
