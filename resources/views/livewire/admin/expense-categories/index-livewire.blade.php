<div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Flash Messages -->
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Xarajat Kategoriyalari</h4>
                        <button class="btn btn-primary" wire:click="createCategory">
                            <i class="mdi mdi-plus"></i> Yangi Kategoriya
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Kategoriya nomi yoki tavsifini qidiring..." 
                                       wire:model.live="search">
                                <span class="input-group-text">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nomi</th>
                                    <th>Tavsif</th>
                                    <th>Holat</th>
                                    <th>Xarajatlar soni</th>
                                    <th>Yaratilgan</th>
                                    <th>Amallar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td>
                                            <strong>{{ $category->name }}</strong>
                                        </td>
                                        <td>{{ $category->description ?? '-' }}</td>
                                        <td>
                                            @if($category->is_active)
                                                <span class="badge bg-success">Faol</span>
                                            @else
                                                <span class="badge bg-secondary">Nofaol</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $category->expenses()->count() }}</span>
                                        </td>
                                        <td>{{ $category->created_at }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        wire:click="editCategory({{ $category->id }})"
                                                        title="Tahrirlash">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-{{ $category->is_active ? 'warning' : 'success' }}" 
                                                        wire:click="toggleStatus({{ $category->id }})"
                                                        title="{{ $category->is_active ? 'Nofaol qilish' : 'Faol qilish' }}">
                                                    <i class="mdi mdi-{{ $category->is_active ? 'eye-off' : 'eye' }}"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        wire:click="deleteCategory({{ $category->id }})"
                                                        wire:confirm="Kategoriyani o'chirishni xohlaysizmi?"
                                                        title="O'chirish">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="mdi mdi-folder-open" style="font-size: 3rem;"></i>
                                                <p class="mt-2">Hech qanday kategoriya topilmadi</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editingCategory ? 'Kategoriyani tahrirlash' : 'Yangi kategoriya' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveCategory">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nomi *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" wire:model="name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Tavsif</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" wire:model="description" rows="3"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Bekor</button>
                    <button type="button" class="btn btn-primary" wire:click="saveCategory">
                        {{ $editingCategory ? 'Yangilash' : 'Yaratish' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <style>
        .modal-backdrop {
            z-index: 1040;
        }
        
        .modal {
            z-index: 1050;
        }
    </style>
</div>
