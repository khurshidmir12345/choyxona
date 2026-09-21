<div>
    <div class="pos-page-head">
        <div class="pos-head-title">
            <h3>Lavozimlar</h3>
            <p>Har bir lavozimga qaysi bo'limlar ochiq bo'lishini belgilang</p>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('employees.index') }}" class="btn btn-inverse-primary btn-rounded">
                <i class="mdi mdi-account-multiple-outline me-1"></i> Xodimlar
            </a>
            <button type="button" class="btn btn-primary btn-rounded" wire:click="createRole">
                <i class="mdi mdi-plus me-1"></i> Lavozim qo'shish
            </button>
        </div>
    </div>

    <div class="row g-3">
        @foreach($roles as $role)
            <div class="col-12 col-md-6 col-xl-4" wire:key="role-{{ $role->id }}">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div class="min-w-0">
                                <h5 class="mb-1 fw-bold text-truncate">
                                    <i class="mdi mdi-shield-account-outline text-primary me-1"></i>{{ $role->name }}
                                </h5>
                                <small class="text-muted">
                                    {{ $role->users_count }} ta xodim
                                    @if($role->is_default) · standart @endif
                                </small>
                            </div>
                            <div class="text-nowrap">
                                <button type="button" class="btn btn-inverse-primary btn-sm"
                                        wire:click="edit({{ $role->id }})" title="Ruxsatlarni o'zgartirish">
                                    <i class="mdi mdi-pencil-outline"></i>
                                </button>
                                @unless($role->is_default)
                                    <x-confirm-button :call="'delete('.$role->id.')'" title="Lavozim o'chirilsinmi?"/>
                                @endunless
                            </div>
                        </div>

                        @php $keys = $role->permissionKeys(); @endphp
                        @if(! $keys)
                            <p class="text-muted small mb-0">Hech qaysi bo'lim ochilmagan.</p>
                        @else
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($catalog as $key => $meta)
                                    @if(in_array($key, $keys, true))
                                        <span class="badge badge-outline-primary">
                                            <i class="mdi {{ $meta['icon'] }}"></i> {{ $meta['label'] }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($showForm)
        <x-modal :title="$roleId ? 'Lavozimni tahrirlash' : 'Yangi lavozim'" icon="mdi-shield-account-outline"
                 subtitle="Ochiq bo'limlarni belgilang" close="closeForm">
            <form wire:submit="save">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nomi</label>
                        <input type="text" wire:model="name" autofocus
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Masalan: Oshpaz">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label mb-0">Ruxsatlar</label>
                        <div>
                            <button type="button" class="btn btn-link btn-sm p-0 me-2" wire:click="toggleAll(true)">Hammasi</button>
                            <button type="button" class="btn btn-link btn-sm p-0 text-muted" wire:click="toggleAll(false)">Tozalash</button>
                        </div>
                    </div>

                    <div class="perm-list">
                        @foreach($catalog as $key => $meta)
                            <label class="perm-item {{ ! empty($permissions[$key]) ? 'is-on' : '' }}" wire:key="perm-{{ $key }}">
                                <input type="checkbox" class="form-check-input" wire:model="permissions.{{ $key }}">
                                <span class="perm-icon"><i class="mdi {{ $meta['icon'] }}"></i></span>
                                <span class="perm-text">
                                    <strong>{{ $meta['label'] }}</strong>
                                    <small>{{ $meta['hint'] }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-inverse-secondary" wire:click="closeForm">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-check me-1"></i> Saqlash</button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
