<div class="container py-3">
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
                         data-status="{{ strtolower(str_replace(' ', '-', $place->status->value ?? 'available')) }}">
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
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1"
             aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><b>{{ $selectedPlace->name }}</b></h5>
                        <button type="button" class="btn-close" wire:click="cancelOrder"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ $selectedPlace->name }} ({{ $selectedPlace->capacity }} kishilik) yangi buyurtma
                            boshlashingiz mumkin.</p>
                        <!-- Add your order form here -->
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancelOrder">Bekor qilish
                        </button>
                        <button type="button" class="btn btn-success" wire:click="createOrder">Boshlash</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
