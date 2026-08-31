<div>
    <x-ui.page-header title="Sozlamalar" subtitle="Profil, xavfsizlik va kompaniya ma'lumotlari"/>

    <div class="mb-4 flex gap-1 rounded-xl border border-ink-200 bg-white p-1 shadow-card">
        @foreach(['profile' => 'Profil', 'security' => 'Xavfsizlik', 'company' => 'Kompaniya'] as $key => $label)
            <button type="button" wire:click="$set('tab', '{{ $key }}')"
                    class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition-colors
                           {{ $tab === $key ? 'bg-brand-600 text-white' : 'text-ink-600 hover:bg-ink-100' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if($tab === 'profile')
        <div class="card max-w-2xl">
            <div class="card-head"><h2 class="card-title">Foydalanuvchi ma'lumotlari</h2></div>
            <form wire:submit="updateProfile" class="space-y-4 p-5">
                <label class="block">
                    <span class="label">Ism</span>
                    <input type="text" wire:model="name" class="input @error('name') input-error @enderror">
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="label">Telefon raqam</span>
                    <input type="text" wire:model="phone_number"
                           class="input tabular @error('phone_number') input-error @enderror"
                           placeholder="+998 90 123 45 67">
                    @error('phone_number') <span class="field-error">{{ $message }}</span> @enderror
                    <span class="hint">Tizimga shu raqam bilan kirasiz.</span>
                </label>

                <div class="border-t border-ink-200/80 pt-4">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check"/>
                        Saqlash
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if($tab === 'security')
        <div class="card max-w-2xl">
            <div class="card-head"><h2 class="card-title">Parolni o'zgartirish</h2></div>
            <form wire:submit="updatePassword" class="space-y-4 p-5">
                <label class="block">
                    <span class="label">Joriy parol</span>
                    <input type="password" wire:model="current_password" autocomplete="current-password"
                           class="input @error('current_password') input-error @enderror">
                    @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="label">Yangi parol</span>
                    <input type="password" wire:model="new_password" autocomplete="new-password"
                           class="input @error('new_password') input-error @enderror">
                    @error('new_password') <span class="field-error">{{ $message }}</span> @enderror
                    <span class="hint">Kamida 8 ta belgi.</span>
                </label>

                <label class="block">
                    <span class="label">Yangi parolni takrorlang</span>
                    <input type="password" wire:model="new_password_confirmation" autocomplete="new-password"
                           class="input">
                </label>

                <div class="border-t border-ink-200/80 pt-4">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="lock"/>
                        Parolni yangilash
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if($tab === 'company')
        <div class="card max-w-3xl">
            <div class="card-head"><h2 class="card-title">Kompaniya</h2></div>

            @if(! $company)
                <x-ui.empty icon="store" title="Kompaniya biriktirilmagan"
                            description="Hisobingizga kompaniya bog'lanmagan. Administratorga murojaat qiling."/>
            @else
                <form wire:submit="updateCompany" class="space-y-4 p-5">
                    <div class="flex items-center gap-4">
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-ink-100">
                            @if($logo)
                                <img src="{{ $logo->temporaryUrl() }}" alt="" class="h-full w-full object-cover">
                            @elseif($company->logoUrl())
                                <img src="{{ $company->logoUrl() }}" alt="" class="h-full w-full object-cover">
                            @else
                                <x-icon name="store" class="h-6 w-6 text-ink-300"/>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <span class="label">Logotip</span>
                            <input type="file" wire:model="logo" accept="image/*"
                                   class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0
                                          file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold
                                          file:text-ink-700 hover:file:bg-ink-200">
                            @error('logo') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="label">Nomi</span>
                            <input type="text" wire:model="company_name"
                                   class="input @error('company_name') input-error @enderror">
                            @error('company_name') <span class="field-error">{{ $message }}</span> @enderror
                        </label>

                        <label class="block">
                            <span class="label">Telefon</span>
                            <input type="text" wire:model="company_phone" class="input tabular">
                            @error('company_phone') <span class="field-error">{{ $message }}</span> @enderror
                        </label>

                        <label class="block">
                            <span class="label">Email</span>
                            <input type="email" wire:model="company_email"
                                   class="input @error('company_email') input-error @enderror">
                            @error('company_email') <span class="field-error">{{ $message }}</span> @enderror
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="label">Manzil</span>
                            <input type="text" wire:model="company_address" class="input">
                            @error('company_address') <span class="field-error">{{ $message }}</span> @enderror
                        </label>

                        <label class="block">
                            <span class="label">Ochilish vaqti</span>
                            <input type="time" wire:model="open_time" class="input tabular">
                        </label>

                        <label class="block">
                            <span class="label">Yopilish vaqti</span>
                            <input type="time" wire:model="close_time" class="input tabular">
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="label">Izoh</span>
                            <textarea wire:model="company_description" rows="3" class="textarea"></textarea>
                            @error('company_description') <span class="field-error">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="border-t border-ink-200/80 pt-4">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check"/>
                            Saqlash
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif
</div>
