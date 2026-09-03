@component('layouts.admin', ['title' => $biz->term('quick_sale')])
    {{--
        Sotuv ekrani. Ko'rinishi avvalgidek, lekin ichkarida serverga bog'lanmagan:
        mahsulotlar/mijozlar brauzer bazasida, sotuv onlaynda darhol serverga,
        oflaynda navbatga yoziladi va internet kelganda sinxronlanadi.
        Mantiq: public/js/quick-sale.js
    --}}
    <div x-data="quickSale({
            snapshotUrl: '{{ route('pos.snapshot') }}',
            syncUrl: '{{ route('pos.sync') }}',
            loginUrl: '{{ route('login') }}',
            printUrl: '{{ route('admin.orders.print', ['id' => '__ID__']) }}'
         })" x-init="init()" x-cloak>

        <div class="pos-page-head">
            <div class="pos-head-title">
                <h3>{{ $biz->term('quick_sale') }}</h3>
                <p>{{ $biz->term('quick_sale_subtitle') }}</p>
            </div>
            <div class="pos-head-tools">
                <span class="net-pill is-offline" x-show="!online" x-cloak>
                    <i class="mdi mdi-wifi-off"></i> Oflayn — sotuvlar brauzerda saqlanadi
                </span>
                <button type="button" class="btn btn-primary btn-rounded" x-show="queue.length" x-cloak
                        x-on:click="sync(true)" :disabled="!online || syncing">
                    <i class="mdi" :class="syncing ? 'mdi-loading mdi-spin' : 'mdi-cloud-upload-outline'"></i>
                    Sinxronlash (<span x-text="queue.length"></span>)
                </button>
            </div>
            <div class="pos-head-actions type-cards" role="radiogroup">
                <button type="button" class="type-card" :class="{ active: tab.orderType === 'takeaway' }" x-on:click="tab.orderType = 'takeaway'">
                    <span class="type-icon"><i class="mdi {{ $biz->term('takeaway_icon') }}"></i></span>
                    <span class="type-text"><strong>{{ $biz->term('takeaway') }}</strong><small>{{ $biz->term('takeaway_hint') }}</small></span>
                    <i class="mdi mdi-check-circle type-check"></i>
                </button>
                <button type="button" class="type-card" :class="{ active: tab.orderType === 'delivery' }" x-on:click="tab.orderType = 'delivery'">
                    <span class="type-icon"><i class="mdi mdi-moped-outline"></i></span>
                    <span class="type-text"><strong>Yetkazib berish</strong><small>Manzilga yetkaziladi</small></span>
                    <i class="mdi mdi-check-circle type-check"></i>
                </button>
            </div>
        </div>

        <template x-if="notice">
            <div class="offline-notice" :class="'is-' + notice.type">
                <i class="mdi" :class="notice.type === 'error' ? 'mdi-alert-circle-outline' : 'mdi-check-circle-outline'"></i>
                <span x-text="notice.text"></span>
                <button type="button" class="cust-icon-btn ms-auto" x-on:click="notice = null"><i class="mdi mdi-close"></i></button>
            </div>
        </template>

        <template x-if="!snapshot">
            <div class="place-empty">
                <i class="mdi" :class="online ? 'mdi-loading mdi-spin' : 'mdi-wifi-off'"></i>
                <h6 x-text="online ? 'Mahsulotlar yuklanmoqda...' : 'Internet yo\'q va mahsulotlar hali yuklanmagan'"></h6>
                <p x-show="!online">Bu qurilmada sotuv ekrani internet bor paytda kamida bir marta ochilgan bo'lishi kerak.</p>
            </div>
        </template>

        <template x-if="snapshot">
        <div class="pos-grid">
            <div>
                {{-- Parallel savatlar: har mijoz uchun alohida yorliq --}}
                <div class="pos-tabs">
                    <template x-for="(t, i) in tabs" :key="t.id">
                        <div class="pos-tab" :class="{ active: t.id === activeTab }">
                            <button type="button" class="pos-tab-main" x-on:click="switchTab(t.id)">
                                <i class="mdi mdi-account-outline"></i>
                                <span class="pos-tab-label" x-text="tabLabel(t, i)"></span>
                                <span class="pos-tab-count" x-show="Object.keys(t.cart).length" x-text="Object.keys(t.cart).length"></span>
                            </button>
                            <button type="button" class="pos-tab-close" x-show="tabs.length > 1 || Object.keys(t.cart).length" x-on:click="closeTab(t.id)" title="Yorliqni yopish">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                    </template>
                    <button type="button" class="pos-tab pos-tab-add" x-on:click="newTab()" title="Yangi mijoz uchun yorliq">
                        <i class="mdi mdi-plus"></i> Yangi
                    </button>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify text-muted"></i></span>
                    <input type="search" class="form-control border-start-0 ps-0" x-model="search"
                           x-on:input.debounce.150ms="autoScan()" x-on:keydown.enter.prevent="scan()"
                           placeholder="Mahsulot nomi yoki kodi bo'yicha qidirish...">
                </div>

                <div class="pos-categories">
                    <button type="button" class="pos-chip" :class="{ active: category === null }" x-on:click="category = null">Barchasi</button>
                    <template x-for="c in snapshot.categories" :key="c.id">
                        <button type="button" class="pos-chip" :class="{ active: category === c.id }" x-on:click="category = c.id" x-text="c.name"></button>
                    </template>
                </div>

                <div class="pos-products">
                    <template x-for="p in visibleProducts()" :key="p.id">
                        <button type="button" class="pos-tile" :class="{ 'in-cart': tab.cart[p.id] }" x-on:click="add(p)">
                            <span class="tile-count" x-show="tab.cart[p.id]" x-text="tab.cart[p.id]?.quantity"></span>
                            <span class="tile-thumb">
                                <template x-if="p.image"><img :src="p.image" alt="" loading="lazy"></template>
                                <template x-if="!p.image"><i class="mdi mdi-image-outline"></i></template>
                            </span>
                            <p class="tile-name" x-text="p.name"></p>
                            <p class="tile-price">
                                <span x-text="money(p.price)"></span>
                                <span class="badge badge-danger ms-1" style="font-size:.65rem" x-show="p.discount > 0" x-text="'-' + p.discount + '%'"></span>
                            </p>
                            <span class="tile-stock" :class="{ 'is-empty': p.stock <= 0 }">
                                <i class="mdi mdi-package-variant-closed"></i> <span x-text="p.stock"></span> dona
                            </span>
                        </button>
                    </template>
                </div>
                <div class="card" x-show="visibleProducts().length === 0">
                    <div class="card-body empty-state">
                        <i class="mdi {{ $biz->term('pos_empty_icon') }}"></i>
                        <h6>Mahsulot topilmadi</h6>
                        <p>Qidiruv so'zini yoki kategoriyani o'zgartirib ko'ring.</p>
                    </div>
                </div>
            </div>

            <div class="pos-cart-wrap">
                <div class="card pos-cart">
                    <div class="cart-head">
                        <h6 class="mb-0 fw-bold"><i class="mdi mdi-cart-outline text-primary me-1"></i> Savat
                            <span class="badge badge-outline-primary ms-1" x-text="Object.keys(tab.cart).length"></span></h6>
                        <span class="fw-bold tabular" x-show="Object.keys(tab.cart).length" x-text="money(total()) + ' so\'m'"></span>
                    </div>

                    <div class="cart-customer">
                        <template x-if="tab.customer">
                            <div class="cust-selected">
                                <span class="cust-avatar" x-text="(tab.customer.name || '?').charAt(0).toUpperCase()"></span>
                                <div class="min-w-0 flex-grow-1">
                                    <p class="cust-name text-truncate" x-text="tab.customer.name"></p>
                                    <p class="cust-phone" x-text="tab.customer.phone || (tab.customer.id ? 'Telefon yo\'q' : 'Yangi mijoz')"></p>
                                </div>
                                <button type="button" class="cust-icon-btn" x-on:click="tab.customer = null"><i class="mdi mdi-close"></i></button>
                            </div>
                        </template>
                        <template x-if="!tab.customer && !customerForm">
                            <div class="cust-picker" x-on:click.outside="customerOpen = false">
                                <div class="cust-search">
                                    <i class="mdi mdi-account-search-outline"></i>
                                    <input type="text" x-model="customerSearch" placeholder="Mijoz: ism yoki telefon" autocomplete="off"
                                           x-on:focus="customerOpen = true" x-on:keydown.escape="customerOpen = false">
                                    <button type="button" class="cust-add" x-on:click="startCustomer()" title="Yangi mijoz"><i class="mdi mdi-account-plus-outline"></i></button>
                                </div>
                                <div class="cust-dropdown" x-show="customerOpen" x-cloak>
                                    <template x-for="c in customerResults()" :key="c.id">
                                        <button type="button" class="cust-item" x-on:click="pickCustomer(c)">
                                            <span class="cust-avatar" x-text="c.name.charAt(0).toUpperCase()"></span>
                                            <span class="min-w-0"><span class="cust-name d-block text-truncate" x-text="c.name"></span><span class="cust-phone" x-text="c.phone || c.address || '—'"></span></span>
                                        </button>
                                    </template>
                                    <div class="cust-empty" x-show="customerResults().length === 0">
                                        <span x-text="customerSearch.trim() === '' ? 'Hali mijoz yo\'q.' : 'Topilmadi.'"></span>
                                        <button type="button" class="btn btn-link btn-sm p-0" x-on:click="startCustomer()">Yangi mijoz qo'shish</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="customerForm">
                            <div class="cust-form">
                                <p class="cust-form-title"><i class="mdi mdi-account-plus-outline"></i> Yangi mijoz</p>
                                <input type="text" class="form-control form-control-sm" x-model="newCustomer.name" placeholder="Ismi *" x-on:keydown.enter.prevent="saveCustomer()">
                                <input type="tel" class="form-control form-control-sm mt-2 tabular" x-model="newCustomer.phone" placeholder="Telefon" x-on:keydown.enter.prevent="saveCustomer()">
                                <input type="text" class="form-control form-control-sm mt-2" x-model="newCustomer.address" placeholder="Manzil (ixtiyoriy)" x-on:keydown.enter.prevent="saveCustomer()">
                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" class="btn btn-primary btn-sm flex-grow-1" x-on:click="saveCustomer()"><i class="mdi mdi-check me-1"></i> Saqlash</button>
                                    <button type="button" class="btn btn-inverse-secondary btn-sm" x-on:click="customerForm = false">Bekor</button>
                                </div>
                            </div>
                        </template>
                        <div class="cust-address" x-show="tab.orderType === 'delivery'">
                            <label class="form-label small fw-semibold text-muted mb-1"><i class="mdi mdi-map-marker-outline"></i> Yetkazish manzili</label>
                            <input type="text" class="form-control form-control-sm" x-model="tab.deliveryAddress" placeholder="Ko'cha, uy, mo'ljal">
                            <div class="cust-address-chips" x-show="knownAddresses().length">
                                <template x-for="a in knownAddresses()" :key="a">
                                    <button type="button" class="chip" :class="{ active: tab.deliveryAddress === a }" x-on:click="tab.deliveryAddress = a" :title="a">
                                        <i class="mdi mdi-map-marker-outline"></i> <span x-text="a.length > 28 ? a.slice(0, 27) + '…' : a"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="card-body empty-state" x-show="Object.keys(tab.cart).length === 0">
                        <i class="mdi mdi-cart-off"></i>
                        <h6>Savat bo'sh</h6>
                        <p>Mahsulot ustiga bosing — u shu yerga tushadi.</p>
                    </div>

                    <div x-show="Object.keys(tab.cart).length > 0">
                        <div class="cart-body">
                            <template x-for="line in Object.values(tab.cart)" :key="line.product_id">
                                <div class="cart-line">
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="cart-line-name text-truncate" x-text="line.name"></p>
                                        <p class="cart-line-price"><span x-text="money(line.price)"></span> so'm
                                            <span class="text-danger fw-semibold" x-show="line.discount > 0" x-text="'-' + line.discount + '%'"></span></p>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        <button type="button" class="btn btn-inverse-primary qty-btn" x-on:click="setQty(line.product_id, line.quantity - 1)"><i class="mdi mdi-minus"></i></button>
                                        <span class="qty-value" x-text="line.quantity"></span>
                                        <button type="button" class="btn btn-inverse-primary qty-btn" x-on:click="setQty(line.product_id, line.quantity + 1)"><i class="mdi mdi-plus"></i></button>
                                    </div>
                                    <div class="text-end flex-shrink-0" style="width: 78px;">
                                        <span class="fw-bold tabular d-block" x-text="money(lineTotal(line))"></span>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-danger" style="font-size:.72rem" x-on:click="remove(line.product_id)">o'chirish</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="cart-foot">
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Chegirma %</label>
                                    <input type="number" min="0" max="100" x-model.number="tab.discount" class="form-control form-control-sm tabular" placeholder="0" onfocus="this.select()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Berilgan pul</label>
                                    <input type="number" min="0" x-model.number="tab.given" class="form-control form-control-sm tabular" placeholder="0" onfocus="this.select()">
                                </div>
                            </div>
                            <div class="cart-total-row"><span class="text-muted">Oraliq jami</span><span class="fw-semibold" x-text="money(subtotal())"></span></div>
                            <div class="cart-total-row text-danger" x-show="tab.discount > 0"><span>Chegirma <span x-text="tab.discount"></span>%</span><span class="fw-semibold" x-text="'-' + money(subtotal() - total())"></span></div>
                            <div class="cart-total-row grand"><span>To'lov</span><span class="text-primary" x-text="money(total())"></span></div>
                            <div class="cart-total-row text-success fw-semibold" x-show="tab.given > 0"><span>Qaytim</span><span x-text="money(Math.max(0, tab.given - total()))"></span></div>
                            <div class="d-grid gap-2 mt-3">
                                <button type="button" class="btn btn-primary btn-lg" x-on:click="finish()" :disabled="finishing">
                                    <i class="mdi" :class="finishing ? 'mdi-loading mdi-spin' : 'mdi-printer'"></i> Sotuvni yakunlash
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Serverga yetib bormagan sotuvlar --}}
                <div class="card mt-3" x-show="queue.length" x-cloak>
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="mdi mdi-cloud-clock-outline text-warning me-1"></i> Sinxronlanmagan sotuvlar (<span x-text="queue.length"></span>)</h6>
                        <template x-for="s in queue.slice().reverse()" :key="s.uuid">
                            <div class="queue-row">
                                <div class="min-w-0">
                                    <span class="fw-semibold tabular" x-text="money(s.total) + ' so\'m'"></span>
                                    <small class="d-block text-muted" x-text="fmtTime(s.created_at) + ' · ' + s.items.length + ' ta'"></small>
                                    <small class="d-block text-danger" x-show="s.error" x-text="s.error"></small>
                                </div>
                                <button type="button" class="cust-icon-btn" title="O'chirish" x-on:click="dropSale(s.uuid)"><i class="mdi mdi-delete-outline"></i></button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        </template>

        {{-- Oflayn chek (onlaynda oddiy chek sahifasi ochiladi) --}}
        <template x-if="receipt">
        <div class="modal fade show d-block pos-modal" style="background: rgba(30,40,61,.55)">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-icon"><i class="mdi mdi-receipt-text-outline"></i></span>
                        <div class="flex-grow-1"><h5 class="modal-title">Sotuv saqlandi</h5><p class="modal-subtitle">Internet kelganda serverga yuboriladi</p></div>
                        <button type="button" class="modal-close" x-on:click="receipt = null"><i class="mdi mdi-close"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="receipt-print">
                            <div class="text-center fw-bold" x-text="snapshot?.company?.name || 'Chek'"></div>
                            <div class="text-center small text-muted mb-2" x-text="fmtTime(receipt.created_at)"></div>
                            <template x-for="i in receipt.items" :key="i.product_id">
                                <div class="d-flex justify-content-between small">
                                    <span x-text="i.name + ' × ' + i.quantity"></span>
                                    <span class="tabular" x-text="money(lineTotal(i))"></span>
                                </div>
                            </template>
                            <div class="d-flex justify-content-between fw-bold mt-2 pt-2 border-top">
                                <span>TO'LOV</span><span class="tabular" x-text="money(receipt.total) + ' so\'m'"></span>
                            </div>
                            <template x-if="receipt.given > receipt.total">
                                <div class="d-flex justify-content-between small text-success">
                                    <span>Qaytim</span><span class="tabular" x-text="money(receipt.given - receipt.total)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-inverse-secondary" x-on:click="receipt = null">Yopish</button>
                        <button type="button" class="btn btn-primary" x-on:click="window.print()"><i class="mdi mdi-printer me-1"></i> Chop etish</button>
                    </div>
                </div>
            </div>
        </div>
        </template>
    </div>

    @push('scripts')
        <script src="{{ asset('js/quick-sale.js') }}?v={{ filemtime(public_path('js/quick-sale.js')) }}"></script>
    @endpush
@endcomponent
