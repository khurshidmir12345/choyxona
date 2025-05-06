@extends('layout.admin.header')

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.orders.index-livewire')
        </div>
    </div>
@endsection
