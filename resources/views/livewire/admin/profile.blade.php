<div class="container-fluid">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- User Profile Section -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-user text-primary me-2"></i>
                        Foydalanuvchi Ma'lumotlari
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="updateProfile">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ism</label>
                                    <input type="text" wire:model="name" class="form-control" required>
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Telefon raqam</label>
                                    <input type="text" wire:model="phone_number" class="form-control" required>
                                    @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model="email" class="form-control" required>
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Saqlash
                            </button>
                        </div>
                    </form>

                    <!-- Password Change Section -->
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Parolni o'zgartirish</h6>
                        <button type="button" wire:click="togglePasswordForm" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-key me-1"></i>
                            {{ $showPasswordForm ? 'Bekor qilish' : 'O\'zgartirish' }}
                        </button>
                    </div>

                    @if($showPasswordForm)
                        <form wire:submit.prevent="updatePassword">
                            <div class="mb-3">
                                <label class="form-label">Joriy parol</label>
                                <input type="password" wire:model="current_password" class="form-control" required>
                                @error('current_password') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Yangi parol</label>
                                        <input type="password" wire:model="new_password" class="form-control" required>
                                        @error('new_password') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Yangi parolni tasdiqlang</label>
                                        <input type="password" wire:model="confirm_password" class="form-control" required>
                                        @error('confirm_password') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-key me-2"></i>Parolni o'zgartirish
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Company Information Section -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-building text-success me-2"></i>
                        Kompaniya Ma'lumotlari
                    </h5>
                </div>
                <div class="card-body">
                    @if($company)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Kompaniya ma'lumotlari</h6>
                            <button type="button" wire:click="toggleCompanyForm" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-edit me-1"></i>
                                {{ $showCompanyForm ? 'Bekor qilish' : 'Tahrirlash' }}
                            </button>
                        </div>

                        @if($showCompanyForm)
                            <form wire:submit.prevent="updateCompany">
                                <div class="mb-3">
                                    <label class="form-label">Kompaniya nomi</label>
                                    <input type="text" wire:model="company_name" class="form-control" required>
                                    @error('company_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Manzil</label>
                                    <textarea wire:model="company_address" class="form-control" rows="2"></textarea>
                                    @error('company_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Telefon</label>
                                            <input type="text" wire:model="company_phone" class="form-control">
                                            @error('company_phone') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" wire:model="company_email" class="form-control">
                                            @error('company_email') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tavsif</label>
                                    <textarea wire:model="company_description" class="form-control" rows="3"></textarea>
                                    @error('company_description') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Saqlash
                                    </button>
                                </div>
                            </form>
                        @else
                            <!-- Display Company Info -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Kompaniya nomi:</label>
                                        <p class="mb-0">{{ $company_name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Telefon:</label>
                                        <p class="mb-0">{{ $company_phone ?: 'Kiritilmagan' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email:</label>
                                        <p class="mb-0">{{ $company_email ?: 'Kiritilmagan' }}</p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Manzil:</label>
                                        <p class="mb-0">{{ $company_address ?: 'Kiritilmagan' }}</p>
                                    </div>
                                </div>
                                @if($company_description)
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Tavsif:</label>
                                            <p class="mb-0">{{ $company_description }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Kompaniya ma'lumotlari topilmadi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Qo'shimcha Ma'lumotlar
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Hisob yaratilgan</h6>
                                <p class="fw-bold">{{ $user->created_at->format('d.m.Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Oxirgi tashrif</h6>
                                <p class="fw-bold">{{ $user->updated_at->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Rol</h6>
                                <p class="fw-bold">{{ $user->role->name ?? 'Aniqlanmagan' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Status</h6>
                                <span class="badge bg-success">Faol</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 