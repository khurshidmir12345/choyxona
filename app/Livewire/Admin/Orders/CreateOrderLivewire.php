<?php

namespace App\Livewire\Admin\Orders;

use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Place;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Collection;
use Livewire\Component;

class CreateOrderLivewire extends Component
{
    public $place;
    public $products;
    public $selectedProducts = [];
    public $orderDiscount = 0;
    public $orderAmount = 0;
    public $orderTotalAmount = 0;
    public $searchQuery = '';
    public $categories;
    public $selectedCategory = null;

    protected $listeners = ['productAdded' => 'calculateTotals', 'setPlace'];

    public function setPlace($data)
    {
        $this->place = $data['place'];
        $this->loadProducts();
        $this->loadCategories();
    }
    public function loadCategories()
    {
        // Load product categories
        $this->categories = ProductCategory::query()->where('company_id', auth()->user()->getCompany()->id)->get();
    }

    public function selectCategory($categoryId = null)
    {
        $this->selectedCategory = $categoryId;
        $this->loadProducts();
    }

    public function loadProducts()
    {
        $query = Product::query()
            ->where('company_id', auth()->user()->getCompany()->id);

        if ($this->searchQuery) {
            $query->where('name', 'like', '%' . $this->searchQuery . '%');
        }

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        $this->products = $query->get();
    }

    public function updatedSearchQuery()
    {
        $this->loadProducts();
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
                    $item['price'] = $product->price * $item['quantity'];
                    $item['total_amount'] = $item['price'] - $item['discount'];
                }
                return $item;
            })->toArray();
        } else {
            // Add new product to the order
            $this->selectedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'discount' => $product->discount ?? 0,
                'quantity' => 1,
                'total_amount' => $product->price - ($product->discount ?? 0),
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
        $product['price'] = $product['quantity'] * ($this->products->firstWhere('id', $product['id'])->price ?? 0);
        $product['total_amount'] = $product['price'] - $product['discount'];

        $this->selectedProducts[$index] = $product;
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        // Calculate order amount (sum of all order details total amounts)
        $this->orderAmount = collect($this->selectedProducts)->sum('total_amount');

        // Calculate order total amount (order amount - order discount)
        $this->orderTotalAmount = $this->orderAmount - $this->orderDiscount;

        if ($this->orderTotalAmount < 0) {
            $this->orderTotalAmount = 0;
        }
    }

    public function updatedOrderDiscount()
    {
        if ($this->orderDiscount < 0) {
            $this->orderDiscount = 0;
        }

        if ($this->orderDiscount > $this->orderAmount) {
            $this->orderDiscount = $this->orderAmount;
        }

        $this->calculateTotals();
    }

    public function saveOrder()
    {
        // Validate that there are products in the order
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Please add at least one product to the order.');
            return;
        }

        // Create the order
        $order = Order::create([
            'company_id' => auth()->user()->getCompany()->id,
            'place_id' => $this->place->id,
            'user_id' => auth()->id(),
            'amount' => $this->orderAmount,
            'total_amount' => $this->orderTotalAmount,
            'discount' => $this->orderDiscount,
            'type' => OrderTypeEnum::Cafe->value,
            'status' => OrderStatusEnum::Opened->value,
        ]);

        // Create order details
        foreach ($this->selectedProducts as $product) {
            OrderDetail::query()->create([
                'order_id' => $order->id,
                'product_id' => $product['id'],
                'worker_id' => auth()->id(), // Default to current user, can be changed later
                'discount' => $product['discount'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'total_amount' => $product['total_amount'],
            ]);
        }

        // Redirect to order view or list
        session()->flash('success', 'Order created successfully!');
        return redirect()->route('admin.orders.show', $order->id);
    }
    public function render()
    {
        return view('livewire.admin.orders.create-order-livewire');
    }
}
