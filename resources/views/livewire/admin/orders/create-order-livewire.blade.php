<div class="container-fluid py-3">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="fw-bold mb-0">
                    <span class="text-muted">New Order:</span> {{ $place->name }}
                </h2>
                <div>
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="history.back()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left side - Product selection -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title fw-bold mb-0">Products</h5>
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control" placeholder="Search products..." wire:model.debounce.300ms="searchQuery">
                            <button class="btn btn-outline-secondary" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="mb-4 categories-scroll">
                        <div class="d-flex gap-2 overflow-auto pb-2">
                            <button class="btn {{ $selectedCategory === null ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm"
                                    wire:click="selectCategory(null)">
                                All Categories
                            </button>
                            @foreach($categories as $category)
                                <button class="btn {{ $selectedCategory === $category->id ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm"
                                        wire:click="selectCategory({{ $category->id }})">
                                    {{ $category->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="row g-3">
                        @forelse($products as $product)
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="card h-100 product-card border-0 shadow-sm" wire:click="addProduct({{ $product->id }})">
                                    <div class="card-body p-3 text-center">
                                        <div class="product-img mb-2">
                                            @if($product->image)
                                                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded">
                                            @else
                                                <div class="placeholder-img rounded bg-light d-flex align-items-center justify-content-center" style="height: 80px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <h6 class="product-name mb-1">{{ $product->name }}</h6>
                                        <div class="product-price">
                                            <span class="fw-bold">{{ number_format($product->price) }}</span>
                                            @if($product->discount > 0)
                                                <small class="text-danger ms-1">-{{ number_format($product->discount) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <div class="mb-3 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h5 class="text-muted">No products found</h5>
                                <p>Try changing your search or category filter</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Order summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title fw-bold mb-0">Order Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($selectedProducts as $index => $product)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ $product['name'] }}</span>
                                            @if($product['discount'] > 0)
                                                <small class="text-danger">Discount: {{ number_format($product['discount']) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="input-group input-group-sm quantity-control">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    wire:click="updateQuantity({{ $index }}, {{ $product['quantity'] - 1 }})">-</button>
                                            <input type="number" class="form-control text-center" value="{{ $product['quantity'] }}" min="1"
                                                   wire:change="updateQuantity({{ $index }}, $event.target.value)">
                                            <button class="btn btn-outline-secondary" type="button"
                                                    wire:click="updateQuantity({{ $index }}, {{ $product['quantity'] + 1 }})">+</button>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ number_format($product['total_amount']) }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-danger" wire:click="removeProduct({{ $index }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="text-muted">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <p class="mt-2">No products added yet</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-top">
                        <div class="mb-3">
                            <label for="orderDiscount" class="form-label fw-medium">Order Discount</label>
                            <input type="number" class="form-control" id="orderDiscount" wire:model="orderDiscount" wire:change="updatedOrderDiscount">
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-medium">{{ number_format($orderAmount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount:</span>
                            <span class="fw-medium text-danger">-{{ number_format($orderDiscount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-5 mt-3">
                            <span>Total:</span>
                            <span>{{ number_format($orderTotalAmount) }}</span>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary w-100 py-2" wire:click="saveOrder"
                                {{ empty($selectedProducts) ? 'disabled' : '' }}>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Complete Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
