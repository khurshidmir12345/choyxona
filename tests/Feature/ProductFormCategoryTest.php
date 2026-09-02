<?php

namespace Tests\Feature;

use App\Livewire\Admin\Products\FormLivewire as ProductForm;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductFormCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_modal_ichidan_kategoriya_yaratiladi_va_tanlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();

        $component = Livewire::test(ProductForm::class)
            ->call('startNewCategory')
            ->set('newCategoryName', '  Salatlar  ')
            ->call('createCategory')
            ->assertHasNoErrors()
            ->assertSet('showNewCategory', false)
            ->assertDispatched('categorySaved');

        $category = ProductCategory::where('company_id', $company->id)->where('name', 'Salatlar')->first();

        $this->assertNotNull($category);
        $component->assertSet('category_id', $category->id);
    }

    public function test_mavjud_kategoriya_takror_yaratilmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $existing = ProductCategory::factory()->for($company)->create(['name' => 'Ichimliklar']);

        Livewire::test(ProductForm::class)
            ->call('startNewCategory')
            ->set('newCategoryName', 'ichimliklar')
            ->call('createCategory')
            ->assertHasNoErrors()
            ->assertSet('category_id', $existing->id);

        $this->assertSame(1, ProductCategory::where('company_id', $company->id)->count());
    }

    public function test_bosh_nom_bilan_kategoriya_yaratilmaydi(): void
    {
        $this->actingAsOwner();

        Livewire::test(ProductForm::class)
            ->call('startNewCategory')
            ->set('newCategoryName', '   ')
            ->call('createCategory')
            ->assertHasErrors(['newCategoryName']);

        $this->assertSame(0, ProductCategory::count());
    }

    public function test_png_rasm_bilan_mahsulot_saqlanadi(): void
    {
        Storage::fake('public');

        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $category = ProductCategory::factory()->for($company)->create();

        Livewire::test(ProductForm::class)
            ->set('name', 'Somsa')
            ->set('price', '3000')
            ->set('sell_price', '5000')
            ->set('category_id', $category->id)
            ->set('image', UploadedFile::fake()->image('somsa.png', 900, 700))
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('productSaved');

        $product = Product::where('company_id', $company->id)->first();

        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }
}
