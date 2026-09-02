<?php

use App\Livewire\Admin\Categories\IndexLivewire as CategoryIndex;
use App\Livewire\Admin\Customers\IndexLivewire as CustomerIndex;
use App\Livewire\Admin\Customers\ShowLivewire as CustomerShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ExpenseCategories\IndexLivewire as ExpenseCategoryIndex;
use App\Livewire\Admin\Expenses\IndexLivewire as ExpenseIndex;
use App\Livewire\Admin\Orders\CreateLivewire as QuickSale;
use App\Livewire\Admin\Orders\DeletedOrdersLivewire as DeletedOrders;
use App\Livewire\Admin\Orders\IndexLivewire as OrderIndex;
use App\Livewire\Admin\Orders\OrderCompleted;
use App\Livewire\Admin\Orders\OrderInCafeLivewire as HallPos;
use App\Livewire\Admin\Places\IndexLivewire as PlaceIndex;
use App\Livewire\Admin\ProductStock\IndexLivewire as StockIndex;
use App\Livewire\Admin\Products\IndexLivewire as ProductIndex;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\Setup\BusinessTypeLivewire as BusinessSetup;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
 * Har bir sahifa to'g'ridan-to'g'ri Livewire komponenti.
 * Ilgari oradagi bo'sh blade fayllar bor edi va marshrut nomlari
 * sahifa mazmuniga mos kelmasdi ("/product/rooms" — joylar ro'yxati).
 */
// Birinchi kirishda biznes turi tanlanadi; shu sahifa tekshiruvdan tashqarida.
Route::middleware('auth')->get('/sozlash/biznes-turi', BusinessSetup::class)->name('setup.business');

Route::middleware(['auth', 'business.chosen'])->group(function () {
    Route::redirect('/home', '/dashboard')->name('home');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Sotuv (zal faqat kafe rejimida)
    Route::middleware('cafe.only')->group(function () {
        Route::get('/pos/zal', HallPos::class)->name('cafe.create');
        Route::get('/pos/zal/{place_id}', HallPos::class)->name('admin.orders.place');
        Route::get('/joylar', PlaceIndex::class)->name('places.index');
    });
    Route::get('/pos/tez-sotuv', QuickSale::class)->name('orders.create');
    Route::get('/buyurtmalar', OrderIndex::class)->name('orders.index');
    Route::get('/buyurtmalar/arxiv', DeletedOrders::class)->name('orders.deleted');
    Route::get('/buyurtmalar/{id}/chek', OrderCompleted::class)->name('admin.orders.print');
    Route::get('/mijozlar', CustomerIndex::class)->name('customers.index');
    Route::get('/mijozlar/{id}', CustomerShow::class)->name('customers.show');

    // Katalog
    Route::get('/mahsulotlar', ProductIndex::class)->name('products.index');
    Route::get('/kategoriyalar', CategoryIndex::class)->name('categories.index');
    Route::get('/zaxira', StockIndex::class)->name('product-stock.index');

    // Moliya
    Route::get('/xarajatlar', ExpenseIndex::class)->name('expenses.index');
    Route::get('/xarajat-kategoriyalari', ExpenseCategoryIndex::class)->name('expense-categories.index');

    // Sozlamalar
    Route::get('/profil', Profile::class)->name('admin.profile');
});

require __DIR__.'/auth.php';
