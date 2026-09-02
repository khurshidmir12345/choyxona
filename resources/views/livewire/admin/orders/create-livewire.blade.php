<div>
    @include('livewire.admin.orders.partials.pos', [
        'mode' => 'quick',
        'heading' => $biz->term('quick_sale'),
        'subheading' => $biz->term('quick_sale_subtitle'),
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
