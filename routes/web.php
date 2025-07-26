<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Orders\CreateOrderLivewire;
use App\Livewire\Admin\Orders\OrderCompleted;
use App\Livewire\Admin\ExpenseCategories\IndexLivewire as ExpenseCategoryIndex;
use App\Livewire\Admin\Expenses\IndexLivewire as ExpenseIndex;
use App\Models\Place;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');

Route::get('/home', function () {
    return view('welcome');
})->middleware('auth')->name('home');


//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Dashboard route
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/admin/profile', function () {return view('admin.profile');})->name('admin.profile');

    Route::prefix('product')->group(function () {
        Route::get('/index', function () {return view('admin.products.index');})->name('products.index');
        Route::get('/categories', function () {return view('admin.categories.index');})->name('categories.index');
        Route::get('/rooms', function () {return view('admin.places.index');})->name('places.index');
        Route::get('/orders', function () {return view('admin.orders.index');})->name('orders.index');
        Route::get('/orders/deleted', function () {return view('admin.orders.deleted');})->name('orders.deleted');
        Route::get('/cafe', function () {return view('admin.orders.cafe');})->name('cafe.create');
    });
    Route::prefix('product-stock')->group(function () {
        Route::get('/product-stock', function () {return view('admin.product-stock.index');})->name('product-stock.index');
    });

    // Expense routes
    Route::prefix('expense-cat')->group(function () {
        Route::get('/expense-cat', function () {return view('admin.expense-categories.index');})->name('expense-categories.index');
    });
    
    Route::prefix('expenses')->group(function () {
        Route::get('/expenses', function () {return view('admin.expenses.index');})->name('expenses.index');
    });

    Route::get('/admin/orders/place/{place_id}', App\Livewire\Admin\Orders\OrderInCafeLivewire::class)->name('admin.orders.place');
    
    // Print route for completed orders
    Route::get('/order/{id}/print', OrderCompleted::class)->name('admin.orders.print');
});

require __DIR__.'/auth.php';
