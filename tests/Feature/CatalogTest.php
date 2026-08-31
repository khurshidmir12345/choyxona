<?php

namespace Tests\Feature;

use App\Casts\ProductStockType;
use App\Livewire\Admin\ProductStock\IndexLivewire as StockIndex;
use App\Livewire\Admin\Products\FormLivewire as ProductForm;
use App\Livewire\Admin\Products\IndexLivewire as ProductIndex;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_qidiruv_boshqa_kompaniya_mahsulotini_kursatmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();

        Product::factory()->forCompany($company)->create(['name' => 'Ko\'k choy', 'code' => 11111]);
        $foreign = Product::factory()->create(['name' => 'Ko\'k choy', 'code' => 22222]);

        // Ilgari "orWhere" guruhlanmagani uchun bu so'rov begona
        // kompaniyaning mahsulotini ham chiqarardi.
        Livewire::test(ProductIndex::class)
            ->set('search', 'Ko\'k choy')
            ->assertDontSee($foreign->formattedCode());
    }

    public function test_mahsulot_uchirilganda_savdo_tarixi_saqlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create();

        $order = Order::create([
            'company_id' => $company->id, 'user_id' => $user->id,
            'amount' => 1000, 'total_amount' => 1000, 'discount' => 0,
            'type' => 'takeaway', 'status' => 'done',
        ]);
        OrderDetail::create([
            'order_id' => $order->id, 'product_id' => $product->id, 'worker_id' => $user->id,
            'quantity' => 1, 'price' => 1000, 'discount' => 0, 'total_amount' => 1000,
        ]);

        Livewire::test(ProductIndex::class)->call('delete', $product->id);

        // Kaskadli qattiq o'chirish buyurtma qatorini ham o'chirib yuborardi.
        $this->assertSame(1, OrderDetail::count());
        $this->assertSoftDeleted($product);
    }

    public function test_kod_kompaniya_ichida_takrorlanmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $category = ProductCategory::factory()->for($company)->create();
        Product::factory()->forCompany($company)->create(['code' => 10500]);

        Livewire::test(ProductForm::class)
            ->set('name', 'Yangi')
            ->set('price', '1000')
            ->set('sell_price', '2000')
            ->set('code', 10500)
            ->set('category_id', $category->id)
            ->call('save')
            ->assertHasErrors(['code' => 'unique']);
    }

    public function test_yangi_mahsulot_saqlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $category = ProductCategory::factory()->for($company)->create();

        Livewire::test(ProductForm::class)
            ->set('name', 'Qora choy')
            ->set('price', '5 000')     // bo'sh joyli kiritish ham qabul qilinadi
            ->set('sell_price', '12 000')
            ->set('category_id', $category->id)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::sole();
        $this->assertSame('Qora choy', $product->name);
        $this->assertSame(5_000, $product->price);
        $this->assertSame(12_000, $product->sell_price);
        $this->assertSame(7_000, $product->extra_price);
    }

    public function test_kirim_zaxirani_oshiradi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 10]);

        Livewire::test(StockIndex::class)
            ->set('product_id', $product->id)
            ->set('quantity', 15)
            ->set('type', 'add')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(25, $product->refresh()->current_stock);
    }

    public function test_harakat_tahrirlanganda_zaxira_qayta_hisoblanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 10]);

        $component = Livewire::test(StockIndex::class)
            ->set('product_id', $product->id)
            ->set('quantity', 15)
            ->set('type', 'add')
            ->call('save');

        $this->assertSame(25, $product->refresh()->current_stock);

        // 15 dan 5 ga o'zgartiramiz: 10 + 5 = 15 bo'lishi kerak.
        $component->call('edit', ProductStock::sole()->id)
            ->set('quantity', 5)
            ->call('save');

        $this->assertSame(15, $product->refresh()->current_stock);
    }

    public function test_harakat_uchirilganda_zaxira_qaytariladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 10]);

        $component = Livewire::test(StockIndex::class)
            ->set('product_id', $product->id)
            ->set('quantity', 20)
            ->set('type', 'add')
            ->call('save');

        $this->assertSame(30, $product->refresh()->current_stock);

        $component->call('delete', ProductStock::sole()->id);

        $this->assertSame(10, $product->refresh()->current_stock);
    }

    public function test_zaxiradan_kup_chiqim_qilib_bulmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 3]);

        Livewire::test(StockIndex::class)
            ->set('product_id', $product->id)
            ->set('quantity', 10)
            ->set('type', ProductStockType::Waste->value)
            ->call('save')
            ->assertHasErrors('quantity');

        $this->assertSame(3, $product->refresh()->current_stock);
    }
}
