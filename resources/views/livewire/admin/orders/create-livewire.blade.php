<div>
    @include('livewire.admin.orders.partials.pos', [
        'mode' => 'quick',
        'heading' => 'Tez sotuv',
        'subheading' => 'Yetkazib berish va olib ketish buyurtmalari',
        'products' => $this->products,
        'categories' => $this->categories,
        'cart' => $cart,
        'subtotal' => $this->subtotal,
        'total' => $this->total,
        'change' => $this->change,
        'discountField' => 'orderDiscount',
        'discountValue' => $orderDiscount,
        'backAction' => null,
    ])
</div>
