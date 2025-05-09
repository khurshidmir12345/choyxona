@extends('layout.admin.header')

@section('styles')
    <style>
        .place-card {
            transition: all 0.2s ease;
            cursor: pointer;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .place-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }

        .place-card:active {
            transform: translateY(0);
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .table-action {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #6c757d;
            font-size: 0.875rem;
            opacity: 0.7;
            transition: all 0.2s ease;
        }

        .place-card:hover .table-action {
            opacity: 1;
            color: #0d6efd;
        }

        /* Status-specific styling */
        .place-card[data-status="occupied"] {
            border-left: 4px solid #dc3545;
        }

        .place-card[data-status="available"] {
            border-left: 4px solid #198754;
        }

        .place-card[data-status="reserved"] {
            border-left: 4px solid #fd7e14;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.orders.order-in-cafe-livewire')
        </div>
        <div class="modal fade" id="crete_order" tabindex="-1" aria-labelledby="show_product"
             aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                @livewire('admin.orders.create-order-livewire')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.addEventListener('livewire:initialized', () => {
            Livewire.on('openCreateOrderModal', (event) => {
                // Open the modal
                const modal = new bootstrap.Modal(document.getElementById('crete_order'));
                modal.show();

                // Emit the place data to the show-livewire component
                Livewire.dispatchTo('admin.products.show-livewire', 'setPlace', {
                    place: event.place
                });
            });
        });
    </script>
@endsection
