@extends('layout.admin.header')
@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.orders.deleted-orders-livewire')
        </div>
    </div>
@endsection 