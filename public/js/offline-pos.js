/*
 * Oflayn kassa (Alpine). Ma'lumotlar localStorage'da:
 *   pos.offline.snapshot — mahsulotlar, kategoriyalar, mijozlar
 *   pos.offline.queue    — serverga yuborilmagan sotuvlar
 *   pos.offline.draft    — hozirgi savat (sahifa yopilsa ham turadi)
 * Sinxronlash: POST /api/pos/sync, har sotuv UUID bilan (takror yozilmaydi).
 */
window.offlinePos = function (opts) {
    const KEY_SNAPSHOT = 'pos.offline.snapshot';
    const KEY_QUEUE = 'pos.offline.queue';
    const KEY_DRAFT = 'pos.offline.draft';

    const load = (k, fallback) => { try { const v = JSON.parse(localStorage.getItem(k)); return v ?? fallback; } catch (e) { return fallback; } };
    const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };
    const uuid = () => (crypto.randomUUID ? crypto.randomUUID() : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => { const r = Math.random() * 16 | 0; return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16); }));
    const xsrf = () => { const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/); return m ? decodeURIComponent(m[1]) : ''; };
    const digits = (s) => String(s || '').replace(/\D/g, '');

    return {
        online: navigator.onLine,
        snapshot: load(KEY_SNAPSHOT, null),
        queue: load(KEY_QUEUE, []),
        loadingSnapshot: false,
        syncing: false,
        notice: null,
        receipt: null,

        orderType: 'takeaway',
        cart: {},
        discount: 0,
        given: 0,
        search: '',
        category: null,
        customer: null,
        customerSearch: '',
        customerOpen: false,
        customerForm: false,
        newCustomer: { name: '', phone: '', address: '' },
        deliveryAddress: '',

        init() {
            const draft = load(KEY_DRAFT, null);
            if (draft) Object.assign(this, draft);

            window.addEventListener('online', () => { this.online = true; this.say('Internet keldi. Sinxronlash mumkin.'); if (this.queue.length) this.sync(); });
            window.addEventListener('offline', () => { this.online = false; this.say('Internet uzildi. Sotuvlar brauzerda saqlanadi.', 'error'); });

            this.$watch('cart', () => this.persistDraft());
            ['orderType', 'discount', 'given', 'customer', 'deliveryAddress'].forEach(f => this.$watch(f, () => this.persistDraft()));

            if (this.online) this.refreshSnapshot(false);
        },

        persistDraft() {
            save(KEY_DRAFT, { orderType: this.orderType, cart: this.cart, discount: this.discount, given: this.given, customer: this.customer, deliveryAddress: this.deliveryAddress });
        },

        say(text, type = 'success') { this.notice = { text, type }; },

        // ------------------------------------------------------- ma'lumotlar

        async refreshSnapshot(manual) {
            if (!this.online) { if (manual) this.say('Internet yo\'q.', 'error'); return; }
            this.loadingSnapshot = true;
            try {
                const r = await fetch(opts.snapshotUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (r.status === 401 || r.status === 419 || r.redirected) { window.location.href = opts.loginUrl; return; }
                if (!r.ok) throw new Error('HTTP ' + r.status);
                this.snapshot = await r.json();
                save(KEY_SNAPSHOT, this.snapshot);
                if (manual) this.say('Ma\'lumotlar yangilandi: ' + this.snapshot.products.length + ' ta mahsulot.');
            } catch (e) {
                if (manual) this.say('Ma\'lumotlarni olib bo\'lmadi. Internetni tekshiring.', 'error');
            } finally {
                this.loadingSnapshot = false;
            }
        },

        snapshotAge() {
            if (!this.snapshot) return 'yo\'q';
            const mins = Math.max(0, Math.round((Date.now() - new Date(this.snapshot.fetched_at).getTime()) / 60000));
            if (mins < 1) return 'hozirgina';
            if (mins < 60) return mins + ' daqiqa oldin';
            const h = Math.round(mins / 60);
            return h < 24 ? h + ' soat oldin' : Math.round(h / 24) + ' kun oldin';
        },

        // ---------------------------------------------------------- mahsulot

        visibleProducts() {
            if (!this.snapshot) return [];
            const q = this.search.trim().toLowerCase();
            return this.snapshot.products.filter(p =>
                (this.category === null || p.category_id === this.category) &&
                (q === '' || p.name.toLowerCase().includes(q) || (p.code || '').toLowerCase().includes(q))
            );
        },

        scan() {
            const q = this.search.trim();
            if (!q || !this.snapshot) return;
            const d = digits(q);
            const hit = this.snapshot.products.find(p => p.code === q.toUpperCase() || (d.length >= 5 && digits(p.code) === d));
            if (hit) { this.add(hit); this.search = ''; this.say(hit.name + ' savatga qo\'shildi.'); }
        },

        add(p) {
            const line = this.cart[p.id];
            this.cart = { ...this.cart, [p.id]: line
                ? { ...line, quantity: line.quantity + 1 }
                : { product_id: p.id, name: p.name, price: p.price, discount: p.discount, quantity: 1 } };
        },

        setQty(id, qty) {
            if (qty < 1) return this.remove(id);
            this.cart = { ...this.cart, [id]: { ...this.cart[id], quantity: qty } };
        },

        remove(id) { const c = { ...this.cart }; delete c[id]; this.cart = c; },

        lineTotal(l) { const gross = l.price * l.quantity; const d = Math.min(100, Math.max(0, l.discount || 0)); return Math.round(gross - gross * d / 100); },
        subtotal() { return Object.values(this.cart).reduce((s, l) => s + this.lineTotal(l), 0); },
        total() { const d = Math.min(100, Math.max(0, Number(this.discount) || 0)); const s = this.subtotal(); return Math.max(0, Math.round(s - s * d / 100)); },
        money(n) { return new Intl.NumberFormat('ru-RU').format(Math.round(n || 0)).replace(/,/g, ' '); },
        fmtTime(iso) { const d = new Date(iso); return d.toLocaleDateString('ru-RU') + ' ' + d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }); },

        // ------------------------------------------------------------ mijoz

        customerResults() {
            if (!this.snapshot) return [];
            const q = this.customerSearch.trim().toLowerCase();
            const d = digits(q);
            return this.snapshot.customers.filter(c => q === '' || c.name.toLowerCase().includes(q) || (d && digits(c.phone).includes(d))).slice(0, 8);
        },
        pickCustomer(c) { this.customer = { id: c.id, name: c.name, phone: c.phone, address: c.address }; this.customerOpen = false; this.customerSearch = ''; if (!this.deliveryAddress && c.address) this.deliveryAddress = c.address; },
        clearCustomer() { this.customer = null; },
        startCustomer() {
            const q = this.customerSearch.trim();
            const isPhone = /^[\d\s+()-]+$/.test(q) && q !== '';
            this.newCustomer = { name: isPhone ? '' : q, phone: isPhone ? q : '', address: '' };
            this.customerForm = true; this.customerOpen = false;
        },
        saveCustomer() {
            if (!this.newCustomer.name.trim()) { this.say('Mijoz ismini yozing.', 'error'); return; }
            this.customer = { id: null, name: this.newCustomer.name.trim(), phone: this.newCustomer.phone.trim(), address: this.newCustomer.address.trim() };
            if (!this.deliveryAddress && this.customer.address) this.deliveryAddress = this.customer.address;
            this.customerForm = false; this.customerSearch = '';
        },

        // ------------------------------------------------------------ sotuv

        finish() {
            if (!Object.keys(this.cart).length) { this.say('Kamida bitta mahsulot tanlang.', 'error'); return; }
            const sale = {
                uuid: uuid(),
                type: this.orderType,
                items: Object.values(this.cart).map(l => ({ product_id: l.product_id, name: l.name, quantity: l.quantity, price: l.price, discount: l.discount || 0 })),
                discount: Math.min(100, Math.max(0, Number(this.discount) || 0)),
                customer_id: this.customer?.id || null,
                customer: this.customer && !this.customer.id ? { name: this.customer.name, phone: this.customer.phone, address: this.customer.address } : null,
                delivery_address: this.orderType === 'delivery' ? (this.deliveryAddress || null) : null,
                created_at: new Date().toISOString(),
                total: this.total(),
                given: Number(this.given) || 0,
            };

            this.queue = [...this.queue, sale];
            save(KEY_QUEUE, this.queue);

            // Ekrandagi qoldiq ham kamaysin — sinxronlashgacha taxminiy.
            if (this.snapshot) {
                sale.items.forEach(i => { const p = this.snapshot.products.find(x => x.id === i.product_id); if (p) p.stock -= i.quantity; });
                save(KEY_SNAPSHOT, this.snapshot);
            }

            this.receipt = sale;
            this.cart = {}; this.discount = 0; this.given = 0; this.customer = null; this.deliveryAddress = '';
            this.persistDraft();
            window.dispatchEvent(new CustomEvent('pos-queue-changed'));

            if (this.online) this.sync();
        },

        dropSale(id) {
            if (!confirm('Bu sotuv o\'chirilsinmi? U serverga yuborilmaydi.')) return;
            this.queue = this.queue.filter(s => s.uuid !== id);
            save(KEY_QUEUE, this.queue);
            window.dispatchEvent(new CustomEvent('pos-queue-changed'));
        },

        async sync() {
            if (!this.online || this.syncing || !this.queue.length) return;
            this.syncing = true;
            try {
                const r = await fetch(opts.syncUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': xsrf() },
                    body: JSON.stringify({ sales: this.queue.map(s => ({ uuid: s.uuid, type: s.type, items: s.items, discount: s.discount, customer_id: s.customer_id, customer: s.customer, delivery_address: s.delivery_address, created_at: s.created_at })) }),
                });
                if (r.status === 401 || r.status === 419) { this.say('Sessiya tugagan. Qayta kiring, sotuvlar saqlanib qoladi.', 'error'); window.location.href = opts.loginUrl; return; }
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const data = await r.json();
                let ok = 0, failed = 0;
                const byUuid = Object.fromEntries((data.results || []).map(x => [x.uuid, x]));
                this.queue = this.queue.filter(s => {
                    const res = byUuid[s.uuid];
                    if (!res) return true;
                    if (res.status === 'created' || res.status === 'duplicate') { ok++; return false; }
                    failed++; s.error = res.message || 'Xatolik'; return true;
                });
                save(KEY_QUEUE, this.queue);
                window.dispatchEvent(new CustomEvent('pos-queue-changed'));
                this.say(ok + ' ta sotuv sinxronlandi' + (failed ? ', ' + failed + ' tasida xatolik' : '') + '.', failed ? 'error' : 'success');
                await this.refreshSnapshot(false);
            } catch (e) {
                this.say('Sinxronlab bo\'lmadi. Internetni tekshirib qayta urinib ko\'ring.', 'error');
            } finally {
                this.syncing = false;
            }
        },
    };
};
