<?php

use App\Http\Controllers\Pos\OfflineSyncController;
use App\Http\Controllers\Telegram\MiniAppController;
use App\Http\Controllers\Telegram\WebhookController;
use App\Livewire\Admin\Categories\IndexLivewire as CategoryIndex;
use App\Livewire\Admin\Customers\IndexLivewire as CustomerIndex;
use App\Livewire\Admin\Customers\ShowLivewire as CustomerShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Employees\IndexLivewire as EmployeeIndex;
use App\Livewire\Admin\ExpenseCategories\IndexLivewire as ExpenseCategoryIndex;
use App\Livewire\Admin\Expenses\IndexLivewire as ExpenseIndex;
use App\Livewire\Admin\Orders\DeletedOrdersLivewire as DeletedOrders;
use App\Livewire\Admin\Orders\IndexLivewire as OrderIndex;
use App\Livewire\Admin\Orders\OrderCompleted;
use App\Livewire\Admin\Orders\OrderInCafeLivewire as HallPos;
use App\Livewire\Admin\Places\IndexLivewire as PlaceIndex;
use App\Livewire\Admin\ProductStock\IndexLivewire as StockIndex;
use App\Livewire\Admin\Products\IndexLivewire as ProductIndex;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\Roles\IndexLivewire as RoleIndex;
use App\Livewire\Admin\Setup\BusinessTypeLivewire as BusinessSetup;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->homeRoute())
        : redirect()->route('login');
});

/*
 * Telegram mini ilova: /tg — kirish sahifasi (bot tugmasidan ochiladi),
 * webhook — botga yozilgan /start ga javob. Ikkalasi ham auth'siz.
 */
Route::get('/tg', [MiniAppController::class, 'entry'])->name('telegram.entry');
Route::post('/tg/auth', [MiniAppController::class, 'auth'])->name('telegram.auth');
Route::post('/tg/login', [MiniAppController::class, 'login'])->name('telegram.login');
Route::post('/api/telegram/webhook', WebhookController::class)->name('telegram.webhook');

/*
 * Har bir sahifa to'g'ridan-to'g'ri Livewire komponenti. Bo'limlar
 * `perm:<kalit>` bilan yopiladi — ega hammasiga kiradi, xodim faqat
 * lavozimida yoqilganlarga (App\Support\Permission).
 */
// Birinchi kirishda biznes turi tanlanadi; shu sahifa tekshiruvdan tashqarida.
Route::middleware('auth')->get('/sozlash/biznes-turi', BusinessSetup::class)->name('setup.business');

Route::middleware(['auth', 'business.chosen'])->group(function () {
    Route::get('/home', fn () => redirect()->route(auth()->user()->homeRoute()))->name('home');
    Route::view('/ruxsat-yoq', 'errors.no-access')->name('no-access');

    Route::middleware('perm:dashboard')->get('/dashboard', Dashboard::class)->name('dashboard');

    // Sotuv (zal faqat kafe rejimida)
    Route::middleware(['cafe.only', 'perm:hall'])->group(function () {
        Route::get('/pos/zal', HallPos::class)->name('cafe.create');
        Route::get('/pos/zal/{place_id}', HallPos::class)->name('admin.orders.place');
    });
    Route::middleware(['cafe.only', 'perm:places'])->get('/joylar', PlaceIndex::class)->name('places.index');

    // Sotuv ekrani: brauzerda ishlaydi, internetsiz ham; sotuvlar API orqali yoziladi
    Route::middleware('perm:quick_sale')->group(function () {
        Route::view('/pos/tez-sotuv', 'pos.quick-sale')->name('orders.create');
        Route::redirect('/pos/oflayn', '/pos/tez-sotuv');
        Route::get('/api/pos/snapshot', [OfflineSyncController::class, 'snapshot'])->name('pos.snapshot');
        Route::post('/api/pos/sync', [OfflineSyncController::class, 'sync'])->name('pos.sync');
    });

    Route::middleware('perm:orders')->group(function () {
        Route::get('/buyurtmalar', OrderIndex::class)->name('orders.index');
        Route::get('/buyurtmalar/arxiv', DeletedOrders::class)->name('orders.deleted');
    });
    // Chek: sotuvdan keyin har kim chiqaradi
    Route::get('/buyurtmalar/{id}/chek', OrderCompleted::class)->name('admin.orders.print');

    Route::middleware('perm:customers')->group(function () {
        Route::get('/mijozlar', CustomerIndex::class)->name('customers.index');
        Route::get('/mijozlar/{id}', CustomerShow::class)->name('customers.show');
    });

    // Katalog
    Route::middleware('perm:products')->group(function () {
        Route::get('/mahsulotlar', ProductIndex::class)->name('products.index');
        Route::get('/kategoriyalar', CategoryIndex::class)->name('categories.index');
    });
    Route::middleware('perm:stock')->get('/zaxira', StockIndex::class)->name('product-stock.index');

    // Moliya
    Route::middleware('perm:expenses')->group(function () {
        Route::get('/xarajatlar', ExpenseIndex::class)->name('expenses.index');
        Route::get('/xarajat-kategoriyalari', ExpenseCategoryIndex::class)->name('expense-categories.index');
    });

    // Xodimlar
    Route::middleware('perm:staff')->group(function () {
        Route::get('/xodimlar', EmployeeIndex::class)->name('employees.index');
        Route::get('/lavozimlar', RoleIndex::class)->name('roles.index');
    });

    // Sozlamalar
    Route::middleware('perm:settings')->get('/profil', Profile::class)->name('admin.profile');
});

require __DIR__.'/auth.php';
