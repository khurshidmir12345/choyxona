@extends('layout.admin.header')

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.product-stock.index-livewire')
        </div>
        <div class="modal fade" id="edit_product_stock" tabindex="-1" aria-labelledby="edit_group" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                @livewire('admin.product-stock.edit-livewire')
            </div>
        </div>
        <div class="modal fade" id="add_product_stock" tabindex="-1" aria-labelledby="add_product_stock"
             aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                @livewire('admin.product-stock.create-livewire')
            </div>
        </div>
{{--        <div class="modal fade" id="show_product" tabindex="-1" aria-labelledby="show_product"--}}
{{--             aria-hidden="true">--}}
{{--            <div class="modal-dialog modal-xl modal-dialog-scrollable">--}}
{{--                @livewire('admin.products.show-livewire')--}}
{{--            </div>--}}
{{--        </div>--}}
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
@endsection
