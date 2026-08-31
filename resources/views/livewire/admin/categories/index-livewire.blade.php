<div>
    <div class="pos-page-head">
        <div>
            <h3>Kategoriyalar</h3>
            <p>Menyu bo'limlari</p>
        </div>
        <div class="pos-head-actions">
            <button type="button" class="btn btn-primary btn-rounded" wire:click="createCategory">
                <i class="mdi mdi-plus me-1"></i> Kategoriya qo'shish
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="input-group" style="max-width: 420px;">
                <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify text-muted"></i></span>
                <input type="search" wire:model.live.debounce.300ms="search"
                       class="form-control border-start-0 ps-0" placeholder="Kategoriya nomi bo'yicha qidirish...">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($categories->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-tag-multiple-outline"></i>
                    <h6>Kategoriya yo'q</h6>
                    <p>Mahsulotlarni guruhlash uchun kategoriya qo'shing.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Nomi</th>
                            <th class="text-center">Mahsulotlar</th>
                            <th>Qo'shilgan</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($categories as $category)
                            <tr wire:key="category-{{ $category->id }}">
                                <td class="text-muted tabular">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td class="text-center">
                                    <span class="badge badge-outline-primary tabular">{{ $category->products_count }}</span>
                                </td>
                                <td class="text-muted tabular">{{ $category->created_at?->format('d.m.Y') ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-inverse-primary btn-sm"
                                            wire:click="edit({{ $category->id }})" title="Tahrirlash">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <x-confirm-button :call="'delete('.$category->id.')'"
                                                      title="Kategoriya o'chirilsinmi?"/>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>

    @if($showForm)
        <x-modal :title="$categoryId ? 'Kategoriyani tahrirlash' : 'Yangi kategoriya'" close="closeForm">
            <form wire:submit="save">
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nomi</label>
                    <input type="text" wire:model="name" autofocus
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="Masalan: Issiq ichimliklar">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-inverse-secondary" wire:click="closeForm">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-check me-1"></i> Saqlash</button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
