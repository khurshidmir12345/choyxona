@extends('layout.admin.header')

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.places.index-livewire')
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.addEventListener('closeModal', event => {
            $('#placeModal').modal('hide');
        });
    </script>
@endsection
