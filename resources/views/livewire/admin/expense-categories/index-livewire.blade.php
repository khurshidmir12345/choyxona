<div>
    <div class="pos-page-head">
        <div>
            <h3>Xarajat kategoriyalari</h3>
            <p>Chiqimlarni guruhlash</p>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('expenses.index') }}" class="btn btn-inverse-primary btn-rounded">
                <i class="mdi mdi-wallet-outline me-1"></i> Xarajatlar
            </a>
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
                       class="form-control border-start-0 ps-0" placeholder="Nomi yoki izohi bo'yicha qidirish...">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($categories->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-folder-outline"></i>
                    <h6>Kategoriya yo'q</h6>
                    <p>Xarajatlarni turlarga ajratish uchun kategoriya qo'shing.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th style="width:60px"></th>
                            <th>Nomi</th>
                            <th>Izoh</th>
                            <th class="text-center">Xarajatlar</th>
                            <th>Holati</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($categories as $category)
                            <tr wire:key="exp-cat-{{ $category->id }}">
                                <td>
                                    <span class="d-inline-block rounded"
                                          style="width:26px;height:26px;background: {{ $category->color ?: '#3b82f6' }}"></span>
                                </td>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td class="text-muted text-truncate" style="max-width:280px">
                                    {{ $category->description ?: '—' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-outline-primary tabular">{{ $category->expenses_count }}</span>
                                </td>
                                <td>
                                    <button type="button" class="badge border-0 {{ $category->is_active ? 'badge-success' : 'badge-secondary' }}"
                                            wire:click="toggleStatus({{ $category->id }})">
                                        {{ $category->is_active ? 'Faol' : 'Nofaol' }}
                                    </button>
                                </td>
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
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomi</label>
                        <input type="text" wire:model="name" autofocus
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Masalan: Kommunal to'lovlar">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Izoh</label>
                        <textarea wire:model="description" rows="3" class="form-control"
                                  placeholder="Ixtiyoriy"></textarea>
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Rang</label>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @foreach(['#1F3BB3', '#7DA0FA', '#4DA761', '#E29E09', '#F3797E', '#7978E9', '#00AAB7'] as $swatch)
                                <button type="button" wire:click="$set('color', '{{ $swatch }}')"
                                        class="rounded border-0 p-0"
                                        style="width:32px;height:32px;background: {{ $swatch }};
                                               outline: {{ $color === $swatch ? '3px solid #1e283d' : 'none' }};
                                               outline-offset: 2px"
                                        aria-label="{{ $swatch }}"></button>
                            @endforeach
                            <input type="color" wire:model="color" class="form-control form-control-color"
                                   style="width:52px">
                        </div>
                        @error('color') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
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
