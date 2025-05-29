@extends('layout.admin.header')

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.product-stock.index-livewire')
        </div>
        <div class="modal fade" id="add_product_stock" tabindex="-1" aria-labelledby="add_product_stock"
             aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                @livewire('admin.product-stock.create-livewire')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.addEventListener('closeModal', event => {
            $('#add_product_stock').modal('hide');
        });
    </script>
    <script>
        Livewire.on('openEditProductModal', () => {
            const modal = new bootstrap.Modal(document.getElementById('edit_product'));
            modal.show();
        });
    </script>
    <script>
        setTimeout(function () {
            const successAlert = document.getElementById('success-alert');
            if (successAlert) successAlert.style.display = 'none';

            const errorAlert = document.getElementById('error-alert');
            if (errorAlert) errorAlert.style.display = 'none';
        }, 4000); // 4 sekunddan keyin yo‘qoladi
    </script>
@endsection
