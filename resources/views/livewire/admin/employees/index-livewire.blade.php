<div>
    <div class="pos-page-head">
        <div class="pos-head-title">
            <h3>Xodimlar</h3>
            <p>Telefon raqam va 8 xonali parol bilan kiradi, lavozimiga qarab bo'limlar ochiladi</p>
        </div>
        <div class="pos-head-tools">
            <div class="head-search">
                <i class="mdi mdi-magnify"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Ismi yoki raqami...">
            </div>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('roles.index') }}" class="btn btn-inverse-primary btn-rounded">
                <i class="mdi mdi-shield-account-outline me-1"></i> Lavozimlar
            </a>
            <button type="button" class="btn btn-primary btn-rounded" wire:click="createEmployee">
                <i class="mdi mdi-account-plus-outline me-1"></i> Xodim qo'shish
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($employees->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-account-multiple-outline"></i>
                    <h6>Xodim yo'q</h6>
                    <p>Ofitsant yoki kassir qo'shing — u o'z raqami va paroli bilan kiradi.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Xodim</th>
                            <th>Telefon</th>
                            <th>Lavozim</th>
                            <th>Telegram</th>
                            <th>Holati</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($employees as $employee)
                            <tr wire:key="emp-{{ $employee->id }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="user-avatar">{{ mb_strtoupper(mb_substr($employee->name ?: 'X', 0, 1)) }}</span>
                                        <span class="fw-semibold">{{ $employee->name }}</span>
                                    </div>
                                </td>
                                <td class="tabular">{{ $employee->phone_number }}</td>
                                <td>
                                    <span class="badge badge-outline-primary">{{ $employee->role?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    @if($employee->telegram_id)
                                        <span class="badge badge-success" title="Telegram orqali kirishi mumkin">
                                            <i class="mdi mdi-send"></i> {{ $employee->telegram_username ? '@'.$employee->telegram_username : 'ulangan' }}
                                        </span>
                                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-muted"
                                                wire:click="unlinkTelegram({{ $employee->id }})" title="Telegramni uzish">
                                            <i class="mdi mdi-link-off"></i>
                                        </button>
                                    @else
                                        <span class="text-muted small">ulanmagan</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="badge border-0 {{ $employee->is_active ? 'badge-success' : 'badge-secondary' }}"
                                            wire:click="toggleActive({{ $employee->id }})" title="Holatni almashtirish">
                                        {{ $employee->is_active ? 'Faol' : 'Nofaol' }}
                                    </button>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-inverse-primary btn-sm"
                                            wire:click="edit({{ $employee->id }})" title="Tahrirlash">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <x-confirm-button :call="'delete('.$employee->id.')'"
                                                      title="Xodim o'chirilsinmi?"
                                                      text="Buyurtmalari bo'lsa, o'chirilmaydi — nofaol qilinadi."/>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $employees->links() }}</div>
            @endif
        </div>
    </div>

    @if($showForm)
        <x-modal :title="$userId ? 'Xodimni tahrirlash' : 'Yangi xodim'" icon="mdi-account-plus-outline"
                 subtitle="Kirish ma'lumotlari va lavozim" close="closeForm">
            <form wire:submit="save">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ismi</label>
                        <input type="text" wire:model="name" autofocus
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Masalan: Aziz">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telefon raqam</label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">+998</span>
                            <input type="tel" wire:model="phone" inputmode="numeric" placeholder="90 123 45 67"
                                   class="form-control tabular @error('phone') is-invalid @enderror">
                        </div>
                        @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lavozim</label>
                        <select wire:model="role_id" class="form-select @error('role_id') is-invalid @enderror">
                            <option value="">Tanlang...</option>
                            @foreach($this->roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Ruxsatlar lavozimda beriladi: <a href="{{ route('roles.index') }}">Lavozimlar</a></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ $userId ? 'Yangi parol (bo\'sh qolsa o\'zgarmaydi)' : 'Parol (8 ta raqam)' }}</label>
                        <div class="input-group">
                            <input type="text" wire:model="password" inputmode="numeric" maxlength="8"
                                   placeholder="12345678"
                                   class="form-control tabular @error('password') is-invalid @enderror">
                            <button type="button" class="btn btn-inverse-primary" wire:click="generatePassword" title="Tasodifiy parol">
                                <i class="mdi mdi-dice-multiple-outline"></i>
                            </button>
                        </div>
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        <div class="form-text">Parolni xodimga ayting — u shu raqam va parol bilan kiradi.</div>
                    </div>

                    @if($userId)
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="emp-active" wire:model="is_active">
                            <label class="form-check-label" for="emp-active">Faol (tizimga kira oladi)</label>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-inverse-secondary" wire:click="closeForm">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-check me-1"></i> Saqlash</button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
