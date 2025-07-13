<?php

namespace App\Livewire\Admin\Orders;

use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductCategory;
use Livewire\Component;

class CreateLivewire extends Component
{
    public $categories;
    public $orderTypes = ['delivery' => 'Dostavka ', 'takeaway' => 'Olib ketish'];
    public $orderType = 'delivery';
    public $company_id;

    public $place;
    public $products;
    public $selectedProducts = [];
    public $orderDiscount;
    public $orderAmount = 0;
    public $orderTotalAmount = 0;
    public $searchQuery = '';
    public $selectedCategory = null;


    public function mount()
    {
        $this->company_id = auth()->user()->getCompany()->id;
        $this->loadCategories();
        $this->loadProducts();
    }

    public function loadCategories()
    {
        $this->categories = ProductCategory::query()->where('company_id', auth()->user()->getCompany()->id)->get();
    }

    public function selectCategory($categoryId = null)
    {
        $this->selectedCategory = $categoryId;
        $this->loadProducts();
    }

    public function loadProducts()
    {
        $this->products = Product::query()
            ->where('company_id', auth()->user()->getCompany()->id)
        ->when($this->selectedCategory, function ($query) {
            return $query->where('category_id', $this->selectedCategory);
        })->when($this->searchQuery, function ($query) {
            return $query->where('name', 'like', '%' . $this->searchQuery . '%');
        })->orderByDesc('id')->get();
    }

    public function updatedSearchQuery()
    {
        $this->loadProducts();
    }

    public function updatedOrderDiscount()
    {
        $this->calculateTotals();
    }

    public function addProduct(Product $product)
    {
        // Check if product already exists in the order
        $existingProduct = collect($this->selectedProducts)->firstWhere('id', $product->id);

        if ($existingProduct) {
            // Increment quantity if product already exists
            $this->selectedProducts = collect($this->selectedProducts)->map(function ($item) use ($product) {
                if ($item['id'] === $product->id) {
                    $item['quantity'] += 1;
                    $item['price'] = $product->sell_price; // Single item price
                    $total_price = $item['price'] * $item['quantity'];
                    $discount_amount = $total_price * ((int)$item['discount'] / 100);
                    $item['total_amount'] = $total_price - (int)$discount_amount;
                }
                return $item;
            })->toArray();

        } else {
            // Add new product to the order
            $this->selectedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sell_price, // Single item price
                'discount' => $product->discount ?? 0,
                'quantity' => 1,
                'total_amount' => $product->sell_price * (1 - ((int)$product->discount ?? 0) / 100),
            ];
        }

        $this->calculateTotals();
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->calculateTotals();
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity < 1) {
            $quantity = 1;
        }

        $product = $this->selectedProducts[$index];
        $product['quantity'] = $quantity;
        $product['price'] = $this->products->firstWhere('id', $product['id'])->sell_price ?? 0; // Single item price
        $total_price = $product['price'] * $product['quantity'];
        $discount_amount = $total_price * ($product['discount'] / 100);
        $product['total_amount'] = (int)$total_price - (int)$discount_amount;

        $this->selectedProducts[$index] = $product;
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        // Calculate order amount (sum of all order details total amounts)
        $this->orderAmount = collect($this->selectedProducts)->sum('total_amount');

        // Calculate order total amount (order amount - order discount)
        $this->orderTotalAmount = (int)$this->orderAmount - ((int)$this->orderAmount * ((int)$this->orderDiscount) / 100);


        if ($this->orderTotalAmount < 0) {
            $this->orderTotalAmount = 0;
        }
    }

    public function saveOrder()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Please add at least one product to the order.');
            return;
        }

        // Create the order
        $order = Order::create([
            'company_id' => auth()->user()->getCompany()->id,
            'place_id' => null,
            'user_id' => auth()->id(),
            'amount' => $this->orderAmount,
            'total_amount' => $this->orderTotalAmount,
            'discount' => $this->orderDiscount ?? 0,
            'type' => $this->orderType,
            'status' => OrderStatusEnum::Done->value,
        ]);

        // Create order details
        foreach ($this->selectedProducts as $product) {
            OrderDetail::query()->create([
                'order_id' => $order->id,
                'product_id' => $product['id'],
                'worker_id' => auth()->id(),
                'discount' => $product['discount'],
                'quantity' => $product['quantity'],
                'price' => $product['price'], // Single item price
                'total_amount' => $product['total_amount'], // Already calculated with discount
            ]);
        }

        // Redirect to order view or list
        session()->flash('success', 'Order created successfully!');
        $this->dispatch('closeModal');
    }

    public function render()
    {
        return view('livewire.admin.orders.create-livewire');
    }
}
