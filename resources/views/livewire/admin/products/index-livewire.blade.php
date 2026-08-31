<div>
    <div class="pos-page-head">
        <div>
            <h3>Mahsulotlar</h3>
            <p>Menyu va narxlar</p>
        </div>
        <div class="pos-head-actions">
            <button type="button" class="btn btn-primary btn-rounded" wire:click="create">
                <i class="mdi mdi-plus me-1"></i> Yangi mahsulot
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold text-muted">Qidirish</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="mdi mdi-magnify text-muted"></i>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search"
                               class="form-control border-start-0 ps-0" placeholder="Nomi yoki kodi...">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted">Kategoriya</label>
                    <select wire:model.live="categoryFilter" class="form-select">
                        <option value="">Barchasi</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($products->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-food-variant"></i>
                    <h6>Mahsulot yo'q</h6>
                    <p>Menyuga birinchi mahsulotni qo'shing.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Kod</th>
                            <th style="width:60px"></th>
                            <th>Nomi</th>
                            <th>Kategoriya</th>
                            <th class="text-end">Tannarx</th>
                            <th class="text-end">Sotuv narxi</th>
                            <th class="text-center">Zaxira</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($products as $product)
                            <tr wire:key="product-{{ $product->id }}">
                                <td class="text-muted tabular small">{{ $product->formattedCode() }}</td>
                                <td>
                                    @if($product->imageUrl())
                                        <img src="{{ $product->imageUrl() }}" alt="" loading="lazy"
                                             class="thumb-sm" onerror="this.remove()">
                                    @else
                                        <span class="thumb-sm d-inline-flex align-items-center justify-content-center">
                                            <i class="mdi mdi-image-outline text-muted"></i>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $product->name }}</span>
                                    @if($product->discount > 0)
                                        <span class="badge badge-danger ms-1">-{{ $product->discount }}%</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->category)
                                        <span class="badge badge-outline-primary">{{ $product->category->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end tabular text-muted">
                                    {{ number_format((int) $product->price, 0, ',', ' ') }}
                                </td>
                                <td class="text-end tabular fw-bold">
                                    {{ number_format((int) $product->sell_price, 0, ',', ' ') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ ($product->current_stock ?? 0) > 0 ? 'badge-success' : 'badge-danger' }} tabular">
                                        {{ (int) ($product->current_stock ?? 0) }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-inverse-primary btn-sm"
                                            wire:click="edit({{ $product->id }})" title="Tahrirlash">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <x-confirm-button :call="'delete('.$product->id.')'"
                                                      title="Mahsulot o'chirilsinmi?"
                                                      text="Savdo tarixi saqlanib qoladi."/>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $products->links() }}</div>
            @endif
        </div>
    </div>

    @if($showForm)
        @livewire('admin.products.form-livewire',
                  ['productId' => $editProductId],
                  key('product-form-'.($editProductId ?? 'new')))
    @endif
</div>
