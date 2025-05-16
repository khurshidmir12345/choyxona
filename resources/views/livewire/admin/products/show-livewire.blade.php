<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="showProductLabel">Mahsulot haqida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-thumbnail">
            </div>
            <div class="col-md-6">
                <h4>{{ $product->name }}</h4>
                <p>{{ $product->description }}</p>
                <p>
                    <b>Narx:</b> {{ number_format($product->price, 0, ',', ' ') }} so'm
                </p>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
    </div>
</div>
