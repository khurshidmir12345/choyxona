<div class="modal-content rounded-3 shadow-lg border-0" style="background: linear-gradient(145deg, #ffffff, #f8f9fa);">
    <div class="modal-header border-0 px-4 py-3">
        <h5 class="modal-title fw-bold text-dark d-flex align-items-center" >
            <i class="bi bi-box-seam me-2" style="color: #007bff;"></i> Mahsulot haqida
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish" wire:click="closeModal()"></button>
    </div>

    <div class="modal-body p-4">
        <div class="text-center mb-4">
            <!-- Mahsulot rasmi -->
            <div class="position-relative d-inline-block">
                <img src="{{ $product->image }}" alt="{{ $product->name }}"
                     class="img-fluid rounded-3 shadow" style="max-height: 280px; object-fit: cover; transition: transform 0.3s ease;">
                <div class="position-absolute top-0 start-0 translate-middle badge bg-primary rounded-pill" style="font-size: 0.9rem;">
                    {{ $product->category->name }}
                </div>
            </div>
        </div>

        <!-- Mahsulot nomi -->
        <h4 class="fw-bold text-dark mb-3 text-center">{{ $product->name }}</h4>

        <!-- Kompaniya va chegirma -->
        <div class="d-flex justify-content-center gap-2 mb-4">
            <span class="badge bg-light text-dark border border-secondary">{{ $product->company->name }}</span>
            @if ($product->discount)
                <span class="badge bg-danger text-white">{{ $product->discount }}% chegirma</span>
            @endif
        </div>

        <!-- Narxlar, zaxira va kod yonma-yon -->
        <div class="row g-3 text-center">
            <div class="col-6">
                <div class="p-2 bg-light rounded-3">
                    <small class="text-muted d-block">Asl narxi:</small>
                    <del class="text-danger fs-6">{{ number_format($product->price, 0, ',', ' ') }} so'm</del>
                </div>
            </div>
            <div class="col-6">
                <div class="p-2 bg-light rounded-3">
                    <small class="text-muted d-block">Sotilayotgan narxi:</small>
                    <span class="text-primary fs-5 fw-bold">{{ number_format($product->sell_price, 0, ',', ' ') }} so'm</span>
                </div>
            </div>
            <div class="col-6">
                <div class="p-2 bg-light rounded-3">
                    <small class="text-muted d-block">Zaxirada:</small>
                    <span class="fw-semibold">{{ $product->current_stock ?? 0}} dona</span>
                </div>
            </div>
            @if ($product->code)
                <div class="col-6">
                    <div class="p-2 bg-light rounded-3">
                        <small class="text-muted d-block">Mahsulot kodi:</small>
                        <span class="fw-semibold">{{ $product->code }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal-footer border-0 px-4 py-3">
        <button type="button" class="btn btn-outline-primary rounded-pill px-4" data-bs-dismiss="modal" wire:click="closeModal()">
            <i class="bi bi-x-circle me-2"></i> Yopish
        </button>
    </div>
</div>
