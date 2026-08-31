<div>
    <div class="pos-page-head">
        <div>
            <h3>Sozlamalar</h3>
            <p>Profil, xavfsizlik va kompaniya ma'lumotlari</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" role="tablist">
                @foreach(['profile' => ['Profil', 'mdi-account-outline'], 'security' => ['Xavfsizlik', 'mdi-lock-outline'], 'company' => ['Kompaniya', 'mdi-store-outline']] as $key => [$label, $icon])
                    <li class="nav-item">
                        <button type="button" wire:click="$set('tab', '{{ $key }}')"
                                class="nav-link {{ $tab === $key ? 'active' : '' }}">
                            <i class="mdi {{ $icon }} me-1"></i> {{ $label }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="pt-4">
                @if($tab === 'profile')
                    <form wire:submit="updateProfile" style="max-width: 560px">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ism</label>
                            <input type="text" wire:model="name"
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Telefon raqam</label>
                            <input type="text" wire:model="phone_number"
                                   class="form-control tabular @error('phone_number') is-invalid @enderror"
                                   placeholder="+998 90 123 45 67">
                            @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Tizimga shu raqam bilan kirasiz.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-check me-1"></i> Saqlash
                        </button>
                    </form>
                @endif

                @if($tab === 'security')
                    <form wire:submit="updatePassword" style="max-width: 560px">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Joriy parol</label>
                            <input type="password" wire:model="current_password" autocomplete="current-password"
                                   class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Yangi parol</label>
                            <input type="password" wire:model="new_password" autocomplete="new-password"
                                   class="form-control @error('new_password') is-invalid @enderror">
                            @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Kamida 8 ta belgi.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Yangi parolni takrorlang</label>
                            <input type="password" wire:model="new_password_confirmation" autocomplete="new-password"
                                   class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-lock-check-outline me-1"></i> Parolni yangilash
                        </button>
                    </form>
                @endif

                @if($tab === 'company')
                    @if(! $company)
                        <div class="empty-state">
                            <i class="mdi mdi-store-outline"></i>
                            <h6>Kompaniya biriktirilmagan</h6>
                            <p>Hisobingizga kompaniya bog'lanmagan. Administratorga murojaat qiling.</p>
                        </div>
                    @else
                        <form wire:submit="updateCompany">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <span class="d-inline-flex align-items-center justify-content-center rounded flex-shrink-0"
                                      style="width:72px;height:72px;background:#f4f6fb;overflow:hidden">
                                    @if($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" alt=""
                                             style="width:100%;height:100%;object-fit:cover">
                                    @elseif($company->logoUrl())
                                        <img src="{{ $company->logoUrl() }}" alt="" onerror="this.remove()"
                                             style="width:100%;height:100%;object-fit:cover">
                                    @else
                                        <i class="mdi mdi-store-outline text-muted" style="font-size:1.6rem"></i>
                                    @endif
                                </span>
                                <div class="flex-grow-1" style="max-width: 420px">
                                    <label class="form-label fw-semibold">Logotip</label>
                                    <input type="file" wire:model="logo" accept="image/*"
                                           class="form-control @error('logo') is-invalid @enderror">
                                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-3" style="max-width: 780px">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Nomi</label>
                                    <input type="text" wire:model="company_name"
                                           class="form-control @error('company_name') is-invalid @enderror">
                                    @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Telefon</label>
                                    <input type="text" wire:model="company_phone" class="form-control tabular">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" wire:model="company_email"
                                           class="form-control @error('company_email') is-invalid @enderror">
                                    @error('company_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Manzil</label>
                                    <input type="text" wire:model="company_address" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Ochilish vaqti</label>
                                    <input type="time" wire:model="open_time" value="{{ $open_time }}"
                                           class="form-control tabular">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Yopilish vaqti</label>
                                    <input type="time" wire:model="close_time" value="{{ $close_time }}"
                                           class="form-control tabular">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Izoh</label>
                                    <textarea wire:model="company_description" rows="3" class="form-control"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="mdi mdi-check me-1"></i> Saqlash
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
