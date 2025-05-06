@extends('layout.admin.header')

@section('content')
    <div class="row">
        <div class="col-12">
            @livewire('admin.categories.index-livewire')
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.addEventListener('closeModal', event => {
            $('#categoryModal').modal('hide');
        });
    </script>
@endsection
