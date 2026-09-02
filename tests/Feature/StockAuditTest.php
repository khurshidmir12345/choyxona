<?php

namespace Tests\Feature;

use App\Casts\ProductStockType;
use App\Livewire\Admin\Orders\CreateLivewire as QuickSale;
use App\Livewire\Admin\ProductStock\IndexLivewire as StockIndex;
use App\Livewire\Admin\Products\FormLivewire as ProductForm;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * "Qoldiq nega o'zgardi?" — har bir o'zgarish jurnalda kim va nima uchun
 * qilgani bilan turishi kerak.
 */
class StockAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_boshlangich_qoldiq_jurnalga_yoziladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $category = ProductCategory::factory()->for($company)->create();

        Livewire::test(ProductForm::class)
            ->set('name', 'Non')
            ->set('price', '1000')
            ->set('sell_price', '2000')
            ->set('category_id', $category->id)
            ->set('initial_stock', 40)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::sole();
        $movement = ProductStock::sole();

        $this->assertSame(40, $product->current_stock);
        $this->assertSame(40, $movement->quantity);
        $this->assertSame(ProductStockType::Add, $movement->type);
        $this->assertSame($user->id, $movement->user_id);
        $this->assertStringContainsString('yaratilganda', $movement->note);
    }

    public function test_tahrirlashda_qoldiq_uzgarishi_sabab_bilan_yoziladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 20]);

        Livewire::test(ProductForm::class, ['productId' => $product->id])
            ->set('current_stock', 15)
            ->set('stock_note', 'inventarizatsiya')
            ->call('save')
            ->assertHasNoErrors();

        $movement = ProductStock::sole();

        $this->assertSame(15, $product->refresh()->current_stock);
        $this->assertSame(5, $movement->quantity);
        $this->assertSame(ProductStockType::Waste, $movement->type);
        $this->assertSame($user->id, $movement->user_id);
        $this->assertStringContainsString('20 → 15', $movement->note);
        $this->assertStringContainsString('inventarizatsiya', $movement->note);
    }

    public function test_qoldiq_uzgarmasa_jurnalga_yozilmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 20]);

        Livewire::test(ProductForm::class, ['productId' => $product->id])
            ->set('name', 'Boshqa nom')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(0, ProductStock::count());
    }

    public function test_kirim_kim_qilgani_va_izohi_bilan_saqlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 0]);

        Livewire::test(StockIndex::class)
            ->set('product_id', $product->id)
            ->set('quantity', 7)
            ->set('type', 'add')
            ->set('note', 'bozordan keldi')
            ->call('save')
            ->assertHasNoErrors();

        $movement = ProductStock::sole();

        $this->assertSame($user->id, $movement->user_id);
        $this->assertSame('bozordan keldi', $movement->note);
    }

    public function test_sotuv_harakati_buyurtma_raqami_bilan_yoziladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 10]);

        Livewire::test(QuickSale::class)
            ->call('addProduct', $product->id)
            ->call('saveOrder');

        $movement = ProductStock::sole();

        $this->assertSame(ProductStockType::Sell, $movement->type);
        $this->assertSame($user->id, $movement->user_id);
        $this->assertStringContainsString('buyurtma #', $movement->note);
    }

    public function test_skaner_kodi_mahsulotni_savatga_qushadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create();

        Livewire::test(QuickSale::class)
            ->set('search', $product->code)
            ->assertSet('search', '')
            ->assertSet('cart.'.$product->id.'.quantity', 1);

        // Prefikssiz raqam ham ishlaydi (skaner faqat raqam yuborsa).
        // Yorliqlar sessiyada saqlanadi — yangi kassa holati uchun tozalaymiz.
        session()->forget('pos.quick.tabs.'.$user->id);

        Livewire::test(QuickSale::class)
            ->set('search', (string) (10_000 + $product->id))
            ->assertSet('cart.'.$product->id.'.quantity', 1);
    }
}
