<div class="container py-3">
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
            </svg>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if($places->isEmpty())
        <div class="text-center py-5">
            <div class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" class="text-muted">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605"/>
                </svg>
            </div>
            <h4 class="text-muted">No tables available</h4>
            <p>There are no tables configured for this cafe</p>
        </div>
    @else
        <div class="row g-4">
            @foreach ($places as $place)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div @if($place->status->value == \App\Casts\PlaceStatusEnum::Busy->value)
                             wire:click="updateOrder({{ $place->id }})"
                         @else
                             wire:click="startOrder({{ $place->id }})"
                         @endif
                         class="card h-100 border-0 shadow-sm place-card"
                         data-status="{{ strtolower(str_replace(' ', '-', $place->status->value ?? 'empty')) }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title fw-bold mb-0">{{ $place->name }}</h5>
                                <span class="status-indicator"
                                      style="background-color: {{ $place->getStatusColor() }}"></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" class="text-muted me-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span><b>{{ $place->capacity }}</b> kishilik</span>
                                </div>
                                <div class="table-action">
                                    <span>Tanlash</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Order Modal -->
    @if($showModal && $selectedPlace)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.6);" tabindex="-1"
             aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-cup-hot me-2" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M.5 6a.5.5 0 0 0-.488.608l1.652 7.434A2.5 2.5 0 0 0 4.104 16h5.792a2.5 2.5 0 0 0 2.44-1.958l.131-.59a3 3 0 0 0 1.3-5.854l.221-.99A.5.5 0 0 0 13.5 6H.5ZM13 12.5a2.01 2.01 0 0 1-.316-.025l.867-3.898A2.001 2.001 0 0 1 13 12.5Z"/>
                                <path d="m4.4.8-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 3.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 3.6 2.8l-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 .6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 .6 2.8l-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 .6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 .6 2.8Z"/>
                            </svg>
                            <b>{{ $selectedPlace->name }}</b>
                        </h5>
                        <button type="button" class="btn-close" wire:click="cancelOrder"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <div class="status-indicator mx-auto mb-3" style="background-color: {{ $selectedPlace->getStatusColor() }}"></div>
                            <h4 class="fw-bold text-primary mb-2">{{ $selectedPlace->name }}</h4>
                            <p class="text-muted mb-3">{{ $selectedPlace->capacity }} kishilik stol</p>
                            <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                </svg>
                                Yangi buyurtma boshlashingiz mumkin
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancelOrder">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-x-circle me-1" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                            Bekor qilish
                        </button>
                        <button type="button" class="btn btn-success" wire:click="createOrder">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-play-circle me-1" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z"/>
                            </svg>
                            Boshlash
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Order Management Modal -->
    @if($showOrderModal && $currentOrder)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.6);" tabindex="-1"
             aria-modal="true" role="dialog">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <span class="place-name-badge">{{ $selectedPlace->name }}</span>
                            <span class="ms-2" style="font-size: 0.9rem;">- Buyurtma #{{ $currentOrder->id }}</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeOrderModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row h-100" style="margin-bottom: 0;">
                            <!-- Products List -->
                            <div class="col-lg-8 col-md-7">
                                <div class="mb-2">
                                    <h6 class="fw-bold mb-1">Mahsulotlar</h6>
                                    <!-- Category Buttons -->
                                    <div class="category-buttons" style="overflow-x: auto; white-space: nowrap; padding-bottom: 5px;">
                                        <button type="button" 
                                                wire:click="$set('selectedCategory', null)"
                                                class="btn btn-sm {{ $selectedCategory === null ? 'btn-primary' : 'btn-outline-primary' }} me-2">
                                            Barchasi
                                        </button>
                                        @foreach($categories as $category)
                                            <button type="button" 
                                                    wire:click="$set('selectedCategory', {{ $category->id }})"
                                                    class="btn btn-sm {{ $selectedCategory == $category->id ? 'btn-primary' : 'btn-outline-primary' }} me-2">
                                                {{ $category->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="row g-2" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                                    @if($products->isEmpty())
                                        <div class="col-12 text-center py-3">
                                            <div class="mb-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-muted">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"/>
                                                </svg>
                                            </div>
                                            <h6 class="text-muted">Bu kategoriyada mahsulot mavjud emas</h6>
                                            <p class="text-muted small">Boshqa kategoriyani tanlang yoki mahsulot qo'shing</p>
                                        </div>
                                    @else
                                        @foreach($products as $product)
                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                <div class="card border-0 shadow-sm h-100 product-card">
                                                    <div class="card-body p-2">
                                                        <!-- Product Image -->
                                                        @if($product->image)
                                                            <div class="text-center mb-2">
                                                                <img src="{{ $product->image }}" 
                                                                     alt="{{ $product->name }}" 
                                                                     class="product-image"
                                                                     onerror="this.style.display='none'">
                                                            </div>
                                                        @endif
                                                        
                                                        <h6 class="card-title mb-1 small">{{ $product->name }}</h6>
                                                        <p class="text-success fw-bold mb-1 small">{{ number_format($product->sell_price, 0, ',', ' ') }} uzs</p>
                                                        
                                                        <!-- Product Details -->
                                                        <div class="mb-1">
                                                            @if($product->discount > 0)
                                                                <span class="badge bg-danger me-1 small">-{{ $product->discount }}%</span>
                                                            @endif
                                                            <span class="badge bg-info small">Stock: {{ $product->current_stock ?? 0 }}</span>
                                                        </div>
                                                        
                                                        <button type="button" class="btn btn-sm btn-primary w-100" 
                                                                wire:click="addProduct({{ $product->id }})">
                                                            + Qo'shish
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="col-lg-4 col-md-5">
                                <h6 class="fw-bold mb-2">Buyurtma</h6>
                                <div class="card border-0 shadow-sm order-summary-card">
                                    <div class="card-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                                        @if(empty($selectedProducts))
                                            <p class="text-muted text-center small">Mahsulot tanlanmagan</p>
                                        @else
                                            @foreach($selectedProducts as $product)
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="d-flex align-items-center">
                                                        <!-- Product Image in Order Summary -->
                                                        @if(isset($product['image']) && $product['image'])
                                                            <img src="{{ $product['image'] }}" 
                                                                 alt="{{ $product['name'] }}" 
                                                                 class="order-product-image me-2"
                                                                 onerror="this.style.display='none'">
                                                        @endif
                                                        <div>
                                                            <small class="fw-bold">{{ $product['name'] }}</small>
                                                            <br>
                                                            <small class="text-muted">{{ number_format($product['price'], 0, ',', ' ') }} uzs</small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" 
                                                                wire:click="updateQuantity({{ $product['id'] }}, {{ $product['quantity'] - 1 }})">-</button>
                                                        <span class="fw-bold">{{ $product['quantity'] }}</span>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-1" 
                                                                wire:click="updateQuantity({{ $product['id'] }}, {{ $product['quantity'] + 1 }})">+</button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" 
                                                                wire:click="removeProduct({{ $product['id'] }})">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <hr class="my-1">
                                            @endforeach

                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <span class="fw-bold">Jami:</span>
                                                <span class="fw-bold text-success">{{ number_format($currentOrder->amount ?? 0, 0, ',', ' ') }} uzs</span>
                                            </div>

                                            <!-- Discount Input -->
                                            <div class="mt-2">
                                                <label class="form-label small">Chegirma (%)</label>
                                                <input type="number" 
                                                       wire:model.live="discount" 
                                                       class="form-control form-control-sm discount-input" 
                                                       min="0" 
                                                       max="100" 
                                                       placeholder="0"
                                                       onfocus="this.select()"
                                                       oninput="handleDiscountInput(this)">
                                            </div>

                                            @if($discount > 0)
                                                <div class="d-flex justify-content-between align-items-center mt-1">
                                                    <span class="text-danger small">Chegirma ({{ $discount }}%):</span>
                                                    <span class="text-danger small">-{{ number_format(($currentOrder->amount ?? 0) * $discount / 100, 0, ',', ' ') }} uzs</span>
                                                </div>
                                            @endif

                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <span class="fw-bold">TO'LOV:</span>
                                                <span class="fw-bold text-primary">{{ number_format($currentOrder->total_amount ?? 0, 0, ',', ' ') }} uzs</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeOrderModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-x-circle me-1" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                            Bekor qilish
                        </button>
                        
                        <button type="button" class="btn btn-outline-danger" wire:click="clearPlace" 
                                onclick="return confirm('Haqiqatan ham bu joyni bo\'shatmoqchimisiz? Barcha buyurtma ma\'lumotlari o\'chiriladi.')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash me-1" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 1 0v6a.5.5 0 0 1-1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>
                            Bo'shatish
                        </button>
                        
                        @if(!empty($selectedProducts))
                            <button type="button" class="btn btn-success" wire:click="saveOrder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-circle me-1" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                </svg>
                                Saqlash
                            </button>
                            <button type="button" class="btn btn-primary" wire:click="closeOrder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-printer me-1" viewBox="0 0 16 16">
                                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                    <path d="M5 1a2 2 0 0 0-2 2v2h10V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3z"/>
                                    <path d="M2.5 11a.5.5 0 1 0 0 1 .5.5 0 0 0 0-1zM5 13a2 2 0 0 0-2 2v2h10v-2a2 2 0 0 0-2-2H5zm-1 2a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4v-2z"/>
                                </svg>
                                Tugatish va Chek
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function handleDiscountInput(input) {
        let value = input.value;
        
        // Agar input bo'sh bo'lsa yoki faqat 0 bo'lsa
        if (value === '' || value === '0') {
            // Livewire'ga 0 yuborish
            @this.set('discount', 0);
            return;
        }
        
        // Agar 0 bilan boshlansa, uni olib tashlash
        if (value.startsWith('0') && value.length > 1) {
            value = value.substring(1);
            input.value = value;
        }
        
        // Sonni tekshirish va cheklash
        let numValue = parseInt(value);
        if (isNaN(numValue)) {
            numValue = 0;
        }
        if (numValue > 100) {
            numValue = 100;
        }
        if (numValue < 0) {
            numValue = 0;
        }
        
        // Input'ga yangi qiymatni qo'yish
        input.value = numValue;
        
        // Livewire'ga yangi qiymatni yuborish
        @this.set('discount', numValue);
    }
    
    // Input focus bo'lganda barcha matnni tanlash
    document.addEventListener('livewire:init', () => {
        Livewire.on('discountUpdated', (data) => {
            // This will be handled by Livewire's real-time updates
        });
    });
</script>
