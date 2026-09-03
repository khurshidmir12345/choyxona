/*
 * Sotuv ekrani (Alpine). Serverga bog'lanmasdan ishlaydi:
 *   pos.quick.snapshot — mahsulotlar, kategoriyalar, mijozlar (brauzer bazasi)
 *   pos.quick.tabs     — yorliqlar (har mijoz uchun alohida savat)
 *   pos.quick.queue    — serverga yetib bormagan sotuvlar
 * Onlaynda sotuv darhol serverga yoziladi va chek sahifasi ochiladi;
 * oflaynda navbatga yoziladi, internet kelganda sinxronlanadi.
 */
window.quickSale = function (opts) {
    const K = { snapshot: 'pos.quick.snapshot', tabs: 'pos.quick.tabs', queue: 'pos.quick.queue' };

    const load = (k, fb) => { try { const v = JSON.parse(localStorage.getItem(k)); return v ?? fb; } catch (e) { return fb; } };
    const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };
    const uuid = () => (crypto.randomUUID ? crypto.randomUUID() : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => { const r = Math.random() * 16 | 0; return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16); }));
    const xsrf = () => { const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/); return m ? decodeURIComponent(m[1]) : ''; };
    const digits = (s) => String(s || '').replace(/\D/g, '');
    const blankTab = (id) => ({ id, orderType: 'takeaway', cart: {}, discount: 0, given: 0, customer: null, deliveryAddress: '' });

    return {
        online: navigator.onLine,
        snapshot: load(K.snapshot, null),
        queue: load(K.queue, []),
        tabs: [],
        activeTab: 1,
        tab: blankTab(1),
        syncing: false,
        finishing: false,
        notice: null,
        receipt: null,
        search: '',
        category: null,
        customerSearch: '',
        customerOpen: false,
        customerForm: false,
        newCustomer: { name: '', phone: '', address: '' },

        init() {
            const saved = load(K.tabs, null);
            if (saved && Array.isArray(saved.tabs) && saved.tabs.length) {
                this.tabs = saved.tabs.map(t => ({ ...blankTab(t.id), ...t }));
                this.activeTab = this.tabs.some(t => t.id === saved.active) ? saved.active : this.tabs[0].id;
            } else {
                this.tabs = [blankTab(1)];
                this.activeTab = 1;
            }
            this.tab = this.tabs.find(t => t.id === this.activeTab);

            this.$watch('tabs', () => this.persist());
            this.$watch('tab', () => this.persist());
            this.$watch('activeTab', () => this.persist());

            window.addEventListener('online', () => { this.online = true; if (this.queue.length) { this.say('Internet keldi. Sotuvlar sinxronlanmoqda...'); this.sync(false); } });
            window.addEventListener('offline', () => { this.online = false; this.say('Internet uzildi. Sotuvlar brauzerda saqlanadi, keyin sinxronlanadi.', 'error'); });

            if (this.online) {
                this.refreshSnapshot();
                if (this.queue.length) this.sync(false);
            }
        },

        persist() { save(K.tabs, { tabs: this.tabs, active: this.activeTab }); },
        say(text, type = 'success') { this.notice = { text, type }; },

        // ------------------------------------------------------- ma'lumotlar

        async refreshSnapshot() {
            try {
                const r = await fetch(opts.snapshotUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (r.status === 401 || r.status === 419 || r.redirected) { window.location.href = opts.loginUrl; return; }
                if (!r.ok) return;
                this.snapshot = await r.json();
                save(K.snapshot, this.snapshot);
            } catch (e) { /* oflayn — eski ma'lumotlar bilan davom etamiz */ }
        },

        // ------------------------------------------------------------ yorliqlar

        tabLabel(t, i) { return t.customer?.name || ('Mijoz ' + (i + 1)); },
        switchTab(id) { const t = this.tabs.find(x => x.id === id); if (t) { this.tab = t; this.activeTab = id; } },
        newTab() { const id = Math.max(0, ...this.tabs.map(t => t.id)) + 1; const t = blankTab(id); this.tabs.push(t); this.switchTab(id); },
        closeTab(id) {
            const t = this.tabs.find(x => x.id === id);
            if (!t) return;
            const n = Object.keys(t.cart).length;
            const doClose = () => {
                this.tabs = this.tabs.filter(x => x.id !== id);
                if (!this.tabs.length) this.tabs = [blankTab(1)];
                if (this.activeTab === id) this.switchTab(this.tabs[0].id);
            };
            if (n === 0) return doClose();
            if (window.Swal) {
                Swal.fire({ title: 'Yorliq yopilsinmi?', text: 'Savatdagi ' + n + ' ta mahsulot o\'chib ketadi.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#F3797E', cancelButtonColor: '#8e94a9', confirmButtonText: 'Ha, yop', cancelButtonText: 'Bekor qilish', reverseButtons: true })
                    .then(r => { if (r.isConfirmed) doClose(); });
            } else if (confirm('Yorliq yopilsinmi? Savatdagi mahsulotlar o\'chib ketadi.')) doClose();
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
        findByCode(q) {
            if (!this.snapshot) return null;
            const d = digits(q);
            return this.snapshot.products.find(p => p.code === q.toUpperCase() || (d.length >= 5 && digits(p.code) === d)) || null;
        },
        /** Skaner to'liq kodni yozsa mahsulot darhol savatga tushadi. */
        autoScan() { const hit = this.findByCode(this.search.trim()); if (hit) { this.add(hit); this.search = ''; this.say(hit.name + ' savatga qo\'shildi.'); } },
        scan() { this.autoScan(); },

        add(p) {
            const line = this.tab.cart[p.id];
            this.tab.cart = { ...this.tab.cart, [p.id]: line
                ? { ...line, quantity: line.quantity + 1 }
                : { product_id: p.id, name: p.name, price: p.price, discount: p.discount, quantity: 1 } };
        },
        setQty(id, qty) { if (qty < 1) return this.remove(id); this.tab.cart = { ...this.tab.cart, [id]: { ...this.tab.cart[id], quantity: qty } }; },
        remove(id) { const c = { ...this.tab.cart }; delete c[id]; this.tab.cart = c; },

        lineTotal(l) { const gross = l.price * l.quantity; const d = Math.min(100, Math.max(0, l.discount || 0)); return Math.round(gross - gross * d / 100); },
        subtotal() { return Object.values(this.tab.cart).reduce((s, l) => s + this.lineTotal(l), 0); },
        total() { const d = Math.min(100, Math.max(0, Number(this.tab.discount) || 0)); const s = this.subtotal(); return Math.max(0, Math.round(s - s * d / 100)); },
        money(n) { return new Intl.NumberFormat('ru-RU').format(Math.round(n || 0)).replace(/,/g, ' '); },
        fmtTime(iso) { const d = new Date(iso); return d.toLocaleDateString('ru-RU') + ' ' + d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }); },

        // ------------------------------------------------------------ mijoz

        customerResults() {
            if (!this.snapshot) return [];
            const q = this.customerSearch.trim().toLowerCase();
            const d = digits(q);
            return this.snapshot.customers.filter(c => q === '' || c.name.toLowerCase().includes(q) || (d && digits(c.phone).includes(d))).slice(0, 8);
        },
        pickCustomer(c) { this.tab.customer = { id: c.id, name: c.name, phone: c.phone, address: c.address }; this.customerOpen = false; this.customerSearch = ''; if (!this.tab.deliveryAddress && c.address) this.tab.deliveryAddress = c.address; },
        knownAddresses() { const c = this.tab.customer; return c && c.address ? [c.address] : []; },
        startCustomer() {
            const q = this.customerSearch.trim();
            const isPhone = /^[\d\s+()-]+$/.test(q) && q !== '';
            this.newCustomer = { name: isPhone ? '' : q, phone: isPhone ? q : '', address: '' };
            this.customerForm = true; this.customerOpen = false;
        },
        saveCustomer() {
            if (!this.newCustomer.name.trim()) { this.say('Mijoz ismini yozing.', 'error'); return; }
            this.tab.customer = { id: null, name: this.newCustomer.name.trim(), phone: this.newCustomer.phone.trim(), address: this.newCustomer.address.trim() };
            if (!this.tab.deliveryAddress && this.tab.customer.address) this.tab.deliveryAddress = this.tab.customer.address;
            this.customerForm = false; this.customerSearch = '';
        },

        // ------------------------------------------------------------ sotuv

        async finish() {
            if (!Object.keys(this.tab.cart).length) { this.say('Kamida bitta mahsulot tanlang.', 'error'); return; }
            const t = this.tab;
            const sale = {
                uuid: uuid(),
                type: t.orderType,
                items: Object.values(t.cart).map(l => ({ product_id: l.product_id, name: l.name, quantity: l.quantity, price: l.price, discount: l.discount || 0 })),
                discount: Math.min(100, Math.max(0, Number(t.discount) || 0)),
                customer_id: t.customer?.id || null,
                customer: t.customer && !t.customer.id ? { name: t.customer.name, phone: t.customer.phone, address: t.customer.address } : null,
                delivery_address: t.orderType === 'delivery' ? (t.deliveryAddress || null) : null,
                created_at: new Date().toISOString(),
                total: this.total(),
                given: Number(t.given) || 0,
            };

            this.queue = [...this.queue, sale];
            save(K.queue, this.queue);
            this.bumpStock(sale, -1);
            this.closeTabAfterSale(t.id);
            window.dispatchEvent(new CustomEvent('pos-queue-changed'));

            if (this.online) {
                this.finishing = true;
                const res = await this.push([sale]);
                this.finishing = false;
                const r = res && res[sale.uuid];
                if (r && (r.status === 'created' || r.status === 'duplicate') && r.order_id) {
                    window.location.href = opts.printUrl.replace('__ID__', r.order_id);
                    return;
                }
            }

            this.receipt = sale;
        },

        closeTabAfterSale(id) {
            this.tabs = this.tabs.filter(x => x.id !== id);
            if (!this.tabs.length) this.tabs = [blankTab(1)];
            this.switchTab(this.tabs[0].id);
        },

        bumpStock(sale, sign) {
            if (!this.snapshot) return;
            sale.items.forEach(i => { const p = this.snapshot.products.find(x => x.id === i.product_id); if (p) p.stock += sign * i.quantity; });
            save(K.snapshot, this.snapshot);
        },

        dropSale(id) {
            const sale = this.queue.find(s => s.uuid === id);
            if (!sale || !confirm('Bu sotuv o\'chirilsinmi? U serverga yuborilmaydi.')) return;
            this.bumpStock(sale, +1);
            this.queue = this.queue.filter(s => s.uuid !== id);
            save(K.queue, this.queue);
            window.dispatchEvent(new CustomEvent('pos-queue-changed'));
        },

        /** Sotuvlarni serverga yuboradi; natijalarni uuid bo'yicha qaytaradi (xato bo'lsa null). */
        async push(sales) {
            try {
                const r = await fetch(opts.syncUrl, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': xsrf() },
                    body: JSON.stringify({ sales: sales.map(s => ({ uuid: s.uuid, type: s.type, items: s.items, discount: s.discount, customer_id: s.customer_id, customer: s.customer, delivery_address: s.delivery_address, created_at: s.created_at })) }),
                });
                if (r.status === 401 || r.status === 419) { this.say('Sessiya tugagan. Qayta kiring — sotuvlar saqlanib qoladi.', 'error'); setTimeout(() => { window.location.href = opts.loginUrl; }, 1500); return null; }
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const data = await r.json();
                const byUuid = Object.fromEntries((data.results || []).map(x => [x.uuid, x]));
                this.queue = this.queue.filter(s => {
                    const res = byUuid[s.uuid];
                    if (!res) return true;
                    if (res.status === 'created' || res.status === 'duplicate') return false;
                    s.error = res.message || 'Xatolik'; return true;
                });
                save(K.queue, this.queue);
                window.dispatchEvent(new CustomEvent('pos-queue-changed'));
                return byUuid;
            } catch (e) {
                return null;
            }
        },

        async sync(manual) {
            if (!this.online || this.syncing || !this.queue.length) return;
            this.syncing = true;
            const before = this.queue.length;
            const res = await this.push(this.queue);
            this.syncing = false;
            if (res === null) { this.say('Sinxronlab bo\'lmadi. Internetni tekshirib qayta urinib ko\'ring.', 'error'); return; }
            const left = this.queue.length;
            this.say((before - left) + ' ta sotuv sinxronlandi' + (left ? ', ' + left + ' tasida xatolik' : '') + '.', left ? 'error' : 'success');
            await this.refreshSnapshot();
        },
    };
};
