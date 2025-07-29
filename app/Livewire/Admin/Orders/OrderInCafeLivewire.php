<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Place;
use App\Models\Order;
use App\Models\Product;
use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use Livewire\Component;

class OrderInCafeLivewire extends Component
{
    public $place_id;
    public $showModal = false;
    public $selectedPlace = null;
    public $currentOrder = null;
    public $showOrderModal = false;
    public $selectedProducts = [];
    public $quantities = [];
    public $discount = 0;
    public $selectedCategory = null;
    public $givenAmount = 0;
    public $changeAmount = 0;

    public function startOrder($place_id)
    {
        $this->place_id = $place_id;
        $this->selectedPlace = Place::query()->find($place_id);
        $this->showModal = true;
    }

    public function updateOrder($place_id)
    {
        $this->place_id = $place_id;
        $this->selectedPlace = Place::query()->find($place_id);
        
        // Find existing open order for this place
        $this->currentOrder = Order::where('place_id', $place_id)
            ->where('status', OrderStatusEnum::Opened)
            ->where('company_id', auth()->user()->getCompany()->id)
            ->first();
        
        if ($this->currentOrder) {
            // Load existing order details
            $orderDetails = $this->currentOrder->orderDetails()->with('product')->get();
            $this->selectedProducts = [];
            
            foreach ($orderDetails as $detail) {
                $this->selectedProducts[$detail->product_id] = [
                    'id' => $detail->product_id,
                    'name' => $detail->product->name,
                    'price' => $detail->price,
                    'image' => $detail->product->image,
                    'quantity' => $detail->quantity
                ];
            }
            
            $this->showOrderModal = true;
        } else {
            // No existing order, create new one
            $this->showModal = true;
        }
    }

    public function createOrder()
    {
        if (!$this->selectedPlace) {
            return;
        }

        // Create new order
        $this->currentOrder = Order::create([
            'company_id' => auth()->user()->getCompany()->id,
            'place_id' => $this->selectedPlace->id,
            'user_id' => auth()->user()->id,
            'type' => OrderTypeEnum::Cafe,
            'status' => OrderStatusEnum::Opened,
            'amount' => 0,
            'total_amount' => 0,
            'discount' => 0,
        ]);

        // Update place status to busy
        $this->selectedPlace->update(['status' => \App\Casts\PlaceStatusEnum::Busy]);

        $this->showModal = false;
        $this->showOrderModal = true;
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        if (!isset($this->selectedProducts[$productId])) {
            $this->selectedProducts[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sell_price, // Single item price
                'image' => $product->image,
                'quantity' => 1
            ];
        } else {
            $this->selectedProducts[$productId]['quantity']++;
        }

        $this->updateOrderTotal();
    }

    public function removeProduct($productId)
    {
        if (isset($this->selectedProducts[$productId])) {
            unset($this->selectedProducts[$productId]);
            $this->updateOrderTotal();
        }
    }

    public function updateQuantity($productId, $quantity)
    {
        if (isset($this->selectedProducts[$productId])) {
            if ($quantity <= 0) {
                $this->removeProduct($productId);
            } else {
                $this->selectedProducts[$productId]['quantity'] = $quantity;
                $this->updateOrderTotal();
            }
        }
    }

    public function updatedDiscount()
    {
        $this->updateOrderTotal();
    }

    public function updatedGivenAmount()
    {
        $this->calculateChange();
    }

    private function updateOrderTotal()
    {
        $total = 0;
        foreach ($this->selectedProducts as $product) {
            $total += $product['price'] * $product['quantity']; // price is single item price
        }

        if ($this->currentOrder) {
            $discountAmount = ($total * $this->discount) / 100;
            $finalTotal = $total - $discountAmount;
            
            $this->currentOrder->update([
                'amount' => $total,
                'total_amount' => $finalTotal,
                'discount' => $this->discount
            ]);
        }

        // Calculate change amount
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $total = 0;
        foreach ($this->selectedProducts as $product) {
            $total += $product['price'] * $product['quantity'];
        }
        
        $discountAmount = ($total * $this->discount) / 100;
        $finalTotal = $total - $discountAmount;
        
        $this->changeAmount = (int)$this->givenAmount - (int)$finalTotal;
        if ($this->changeAmount < 0) {
            $this->changeAmount = 0;
        }
    }

    public function saveOrder()
    {
        if (!$this->currentOrder || empty($this->selectedProducts)) {
            return;
        }

        // Clear existing order details
        $this->currentOrder->orderDetails()->delete();

        // Save new order details
        foreach ($this->selectedProducts as $product) {
            $this->currentOrder->orderDetails()->create([
                'product_id' => $product['id'],
                'worker_id' => auth()->user()->id,
                'quantity' => $product['quantity'],
                'price' => $product['price'], // Single item price
                'total_amount' => $product['price'] * $product['quantity'], // price * quantity
                'discount' => 0,
            ]);
        }

        $orderId = $this->currentOrder->id;
        $this->closeOrderModal();
        $this->dispatch('orderCreated', orderId: $orderId);
    }

    public function closeOrder()
    {
        if (!$this->currentOrder) {
            return;
        }

        $orderId = $this->currentOrder->id;
        
        // Save order details before closing
        if (!empty($this->selectedProducts)) {
            // Clear existing order details
            $this->currentOrder->orderDetails()->delete();
            
            // Save new order details
            foreach ($this->selectedProducts as $product) {
                $this->currentOrder->orderDetails()->create([
                    'product_id' => $product['id'],
                    'worker_id' => auth()->user()->id,
                    'quantity' => $product['quantity'],
                    'price' => $product['price'], // Single item price
                    'total_amount' => $product['price'] * $product['quantity'], // price * quantity
                    'discount' => 0,
                ]);
            }
        }
        
        $this->currentOrder->update(['status' => OrderStatusEnum::Done]);
        
        // Update place status to empty
        if ($this->selectedPlace) {
            $this->selectedPlace->update(['status' => \App\Casts\PlaceStatusEnum::Empty->value]);
        }

        $this->closeOrderModal();
        
        // Redirect to print page
        return redirect()->route('admin.orders.print', $orderId);
    }

    public function closeOrderModal()
    {
        $this->showOrderModal = false;
        $this->currentOrder = null;
        $this->selectedProducts = [];
        $this->selectedPlace = null;
        $this->place_id = null;
        $this->givenAmount = 0;
        $this->changeAmount = 0;
        $this->discount = 0;
    }

    public function cancelOrder()
    {
        $this->place_id = '';
        $this->showModal = false;
        $this->selectedPlace = null;
    }

    public function clearPlace()
    {
        if (!$this->currentOrder || !$this->selectedPlace) {
            return;
        }

        // Delete the order and its details
        $this->currentOrder->orderDetails()->delete();
        $this->currentOrder->delete();

        // Update place status to empty
        $this->selectedPlace->update(['status' => \App\Casts\PlaceStatusEnum::Empty->value]);

        // Close modal and reset
        $this->closeOrderModal();
        
        // Show success message
        session()->flash('message', 'Joy muvaffaqiyatli bo\'shatildi!');
    }

    public function render()
    {
        $places = Place::query()->where('company_id', auth()->user()->getCompany()->id)->get();
        $categories = \App\Models\ProductCategory::query()->where('company_id', auth()->user()->getCompany()->id)->get();
        
        $productsQuery = Product::query()->where('company_id', auth()->user()->getCompany()->id);
        
        if ($this->selectedCategory) {
            $productsQuery->where('category_id', $this->selectedCategory);
        }
        
        $products = $productsQuery->get();
        
        return view('livewire.admin.orders.order-in-cafe-livewire', compact('places', 'products', 'categories'));
    }
}
