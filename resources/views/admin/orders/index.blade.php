@extends('layout.admin.header')
@section('styles')
    <style>
        body.modal-open{
            overflow: hidden !important;
            padding-right: 0 !important;
        }
        .modal-fullscreen .modal-content {
            padding-right: 20px;
            overflow-y: auto;
            border: none;
            border-radius: 0;
        }
        .btn-xs {
            padding: 0.1rem 0.3rem;
            font-size: 0.75rem;
            line-height: 1.5;
        }
        .form-control-xs {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.5;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.orders.index-livewire')
        </div>
        <div class="modal fade" id="createOrder" tabindex="-1" aria-labelledby="edit_group" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content modal-dialog-scrollable">
                    @livewire('admin.orders.create-livewire')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        window.addEventListener('closeModal', event => {
            $('#createOrder').modal('hide');
        });
    </script>
@endsection
