<?php

namespace Tests\Feature;

use App\Livewire\Admin\Categories\IndexLivewire as CategoryIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ExpenseCategories\IndexLivewire as ExpenseCategoryIndex;
use App\Livewire\Admin\Expenses\IndexLivewire as ExpenseIndex;
use App\Livewire\Admin\Orders\CreateLivewire as QuickSale;
use App\Livewire\Admin\Orders\DeletedOrdersLivewire as Archive;
use App\Livewire\Admin\Orders\IndexLivewire as OrderIndex;
use App\Livewire\Admin\Orders\OrderInCafeLivewire as HallPos;
use App\Livewire\Admin\Places\IndexLivewire as PlaceIndex;
use App\Livewire\Admin\ProductStock\IndexLivewire as StockIndex;
use App\Livewire\Admin\Products\IndexLivewire as ProductIndex;
use App\Livewire\Admin\Profile;
use App\Models\Company;
use App\Models\Place;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Har bir ekran va undagi modal oynalar xatosiz chizilishini tekshiradi.
 * Shablon almashtirilgandan keyin blade'da qolib ketgan eski
 * o'zgaruvchilarni shu test ushlab qoladi.
 */
class ScreensRenderTest extends TestCase
{
    use RefreshDatabase;

    private function seedCompany(): Company
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->firstOrFail();

        Product::factory()->forCompany($company)->create();
        Place::factory()->for($company)->create();

        return $company;
    }

    public function test_barcha_ekranlar_chiziladi(): void
    {
        $this->seedCompany();

        foreach ([
            Dashboard::class, HallPos::class, QuickSale::class, OrderIndex::class,
            Archive::class, ProductIndex::class, CategoryIndex::class, StockIndex::class,
            PlaceIndex::class, ExpenseIndex::class, ExpenseCategoryIndex::class, Profile::class,
        ] as $component) {
            Livewire::test($component)->assertOk();
        }
    }

    public function test_modal_oynalari_ochiladi(): void
    {
        $this->seedCompany();

        Livewire::test(ProductIndex::class)->call('create')->assertSet('showForm', true)->assertOk();
        Livewire::test(CategoryIndex::class)->call('createCategory')->assertSee('Yangi kategoriya');
        Livewire::test(PlaceIndex::class)->call('createPlace')->assertSee('Yangi joy');
        Livewire::test(StockIndex::class)->call('createMovement')->assertSee('Yangi harakat');
        Livewire::test(ExpenseIndex::class)->call('createExpense')->assertSee('Yangi xarajat');
        Livewire::test(ExpenseCategoryIndex::class)->call('createCategory')->assertSee('Yangi kategoriya');
    }

    public function test_zal_buyurtma_ekrani_chiziladi(): void
    {
        $company = $this->seedCompany();
        $place = Place::forCompany($company->id)->firstOrFail();
        $product = Product::forCompany($company->id)->firstOrFail();

        Livewire::test(HallPos::class)
            ->call('openTable', $place->id)
            ->assertSee('Savat')
            ->call('addProduct', $product->id)
            ->assertSee($product->name)
            ->assertOk();
    }

    public function test_profil_bulimlari_chiziladi(): void
    {
        $this->seedCompany();

        Livewire::test(Profile::class)
            ->assertSee('Profil')
            ->set('tab', 'security')->assertSee('Joriy parol')
            ->set('tab', 'company')->assertSee('Logotip');
    }
}
