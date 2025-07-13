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
                        <h4 class="card-title mb-0">Xarajatlar</h4>
                        <button class="btn btn-primary" wire:click="createExpense">
                            <i class="mdi mdi-plus"></i> Yangi Xarajat
                        </button>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Jami Xarajat</h6>
                                            <h4 class="mb-0">{{ number_format($totalAmount, 0, ',', ' ') }} so'm</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="mdi mdi-cash-multiple" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Kutilayotgan</h6>
                                            <h4 class="mb-0">{{ number_format($pendingAmount, 0, ',', ' ') }} so'm</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="mdi mdi-clock" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Tasdiqlangan</h6>
                                            <h4 class="mb-0">{{ number_format($approvedAmount, 0, ',', ' ') }} so'm</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="mdi mdi-check-circle" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Xarajat nomi yoki tavsifini qidiring..." 
                                       wire:model.live="search">
                                <span class="input-group-text">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" wire:model.live="selectedCategory">
                                <option value="">Barcha kategoriyalar</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" wire:model.live="selectedStatus">
                                <option value="">Barcha holatlar</option>
                                <option value="pending">Kutilayotgan</option>
                                <option value="approved">Tasdiqlangan</option>
                                <option value="rejected">Rad etilgan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" wire:model.live="dateFrom" placeholder="Dan">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" wire:model.live="dateTo" placeholder="Gacha">
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-outline-secondary" wire:click="clearFilters" title="Filtrlarni tozalash">
                                <i class="mdi mdi-refresh"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Expenses Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nomi</th>
                                    <th>Kategoriya</th>
                                    <th>Miqdori</th>
                                    <th>Sana</th>
                                    <th>To'lov usuli</th>
                                    <th>Holat</th>
                                    <th>Yaratgan</th>
                                    <th>Amallar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                    <tr>
                                        <td>{{ $expense->id }}</td>
                                        <td>
                                            <strong>{{ $expense->title }}</strong>
                                            @if($expense->description)
                                                <br><small class="text-muted">{{ Str::limit($expense->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $expense->category->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-primary">{{ number_format($expense->amount, 0, ',', ' ') }} so'm</strong>
                                        </td>
                                        <td>{{ $expense->expense_date }}</td>
                                        <td>{{ $expense->payment_method }}</td>
                                        <td>
                                            @if($expense->status === 'pending')
                                                <span class="badge bg-warning">Kutilayotgan</span>
                                            @elseif($expense->status === 'approved')
                                                <span class="badge bg-success">Tasdiqlangan</span>
                                            @else
                                                <span class="badge bg-danger">Rad etilgan</span>
                                            @endif
                                        </td>
                                        <td>{{ $expense->user->name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        wire:click="editExpense({{ $expense->id }})"
                                                        title="Tahrirlash">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                
                                                <!-- Status Dropdown -->
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                            data-bs-toggle="dropdown" title="Holatni o'zgartirish">
                                                        <i class="mdi mdi-flag"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" wire:click="updateStatus({{ $expense->id }}, 'pending')">Kutilayotgan</a></li>
                                                        <li><a class="dropdown-item" href="#" wire:click="updateStatus({{ $expense->id }}, 'approved')">Tasdiqlash</a></li>
                                                        <li><a class="dropdown-item" href="#" wire:click="updateStatus({{ $expense->id }}, 'rejected')">Rad etish</a></li>
                                                    </ul>
                                                </div>
                                                
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        wire:click="deleteExpense({{ $expense->id }})"
                                                        wire:confirm="Xarajatni o'chirishni xohlaysizmi?"
                                                        title="O'chirish">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="mdi mdi-cash-remove" style="font-size: 3rem;"></i>
                                                <p class="mt-2">Hech qanday xarajat topilmadi</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $expenses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editingExpense ? 'Xarajatni tahrirlash' : 'Yangi xarajat' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveExpense">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Nomi *</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" wire:model="title" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Miqdori *</label>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" wire:model="amount" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expense_category_id" class="form-label">Kategoriya *</label>
                                    <select class="form-select @error('expense_category_id') is-invalid @enderror" 
                                            id="expense_category_id" wire:model="expense_category_id" required>
                                        <option value="">Kategoriyani tanlang</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('expense_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expense_date" class="form-label">Sana *</label>
                                    <input type="date" class="form-control @error('expense_date') is-invalid @enderror" 
                                           id="expense_date" wire:model="expense_date" required>
                                    @error('expense_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">To'lov usuli *</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror" 
                                            id="payment_method" wire:model="payment_method" required>
                                        <option value="">To'lov usulini tanlang</option>
                                        <option value="Naqd pul">Naqd pul</option>
                                        <option value="Plastik karta">Plastik karta</option>
                                        <option value="Bank o'tkazmasi">Bank o'tkazmasi</option>
                                        <option value="Click">Click</option>
                                        <option value="Payme">Payme</option>
                                        <option value="Boshqa">Boshqa</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Holat *</label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" wire:model="status" required>
                                        <option value="pending">Kutilayotgan</option>
                                        <option value="approved">Tasdiqlangan</option>
                                        <option value="rejected">Rad etilgan</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
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
                    <button type="button" class="btn btn-primary" wire:click="saveExpense">
                        {{ $editingExpense ? 'Yangilash' : 'Yaratish' }}
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
