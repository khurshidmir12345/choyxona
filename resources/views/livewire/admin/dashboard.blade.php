<div>
    <h1 class="content-title">Welcome to the Admin Panel</h1>
    @foreach($categories as $category)
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $category->name }}</h5>
                <p class="card-text">{{ $category->description }}</p>
                <input type="text" wire:model.live="search">
            </div>
        </div>
    </div>
    @endforeach
</div>

