@extends('layout.admin.header')
@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.products.index-livewire')
        </div>
        <div class="modal fade" id="add_product" tabindex="-1" aria-labelledby="add_product"
             aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                @livewire('admin.products.create-livewire')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.addEventListener('closeModal', event => {
            $('#add_product').modal('hide');
        });
        
        // Modal ochilganda xabarlarni tozalash
        Livewire.on('clearCreateMessages', () => {
            // Xabarlarni tozalash uchun event
        });
    </script>
    <script>
        Livewire.on('openEditProductModal', () => {
            const modal = new bootstrap.Modal(document.getElementById('edit_product'));
            modal.show();
        });
    </script>
@endsection
