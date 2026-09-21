/*
 * Mobil zal POS — brauzer tomonidagi mantiq (Alpine).
 *
 * Stol ochilganda menyu (products) va kategoriyalar bir marta keladi.
 * Savat, miqdor, filtr, qidiruv va summalar shu yerda hisoblanadi —
 * serverga so'rov ketmaydi. cart / discount / given Livewire bilan
 * bog'langan ($wire.entangle): keyingi server chaqiruvi (saqlash, yopish)
 * bilan birga yuboriladi, server esa narxlarni bazadan qayta oladi.
 */
window.mHall = function (opts) {
    const fmt = (n) => String(Math.round(Number(n) || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    const clampPct = (v) => Math.max(0, Math.min(100, Number(v) || 0));

    return {
        products: opts.products || [],
        categories: opts.categories || [],
        cart: opts.cart,
        discount: opts.discount,
        given: opts.given,
        search: '',
        category: null,
        onlyCart: false,
        sheet: null,

        init() {
            // Livewire bo'sh PHP massivini [] deb yuboradi — obyekt kerak.
            if (!this.cart || Array.isArray(this.cart)) this.cart = {};
        },

        // ---------------------------------------------------------- ro'yxat
        get list() {
            const q = this.search.trim().toLowerCase();

            return this.products.filter((p) =>
                (!this.onlyCart || this.qty(p.id) > 0)
                && (this.category === null || p.category_id === this.category)
                && (!q || p.name.toLowerCase().includes(q) || String(p.code || '').toLowerCase().includes(q))
            );
        },

        setCategory(id) { this.category = id; this.onlyCart = false; },
        toggleOnlyCart() { this.onlyCart = !this.onlyCart; },

        // ------------------------------------------------------------ savat
        get lines() { return Object.values(this.cart || {}); },
        get kinds() { return this.lines.length; },
        get count() { return this.lines.reduce((s, l) => s + (Number(l.quantity) || 0), 0); },

        qty(id) { const l = this.cart && this.cart[id]; return l ? Number(l.quantity) || 0 : 0; },

        setLine(id, line) { this.cart = { ...(this.cart || {}), [id]: line }; },

        add(id) {
            const line = this.cart && this.cart[id];
            if (line) return this.setLine(id, { ...line, quantity: Number(line.quantity) + 1 });

            const p = this.products.find((x) => x.id === id);
            if (!p) return;

            this.setLine(id, { product_id: p.id, name: p.name, price: p.price, discount: p.discount, quantity: 1 });
        },

        inc(id) { this.add(id); },

        dec(id) {
            const line = this.cart && this.cart[id];
            if (!line) return;
            if (Number(line.quantity) <= 1) return this.remove(id);
            this.setLine(id, { ...line, quantity: Number(line.quantity) - 1 });
        },

        remove(id) {
            const c = { ...(this.cart || {}) };
            delete c[id];
            this.cart = c;
            if (!Object.keys(c).length) { this.onlyCart = false; this.sheet = null; }
        },

        // ---------------------------------------------------------- summalar
        lineTotal(l) {
            const gross = (Number(l.price) || 0) * (Number(l.quantity) || 0);
            return Math.round(gross - gross * clampPct(l.discount) / 100);
        },
        get subtotal() { return this.lines.reduce((s, l) => s + this.lineTotal(l), 0); },
        get discountAmount() { return Math.round(this.subtotal * clampPct(this.discount) / 100); },
        get total() { return Math.max(0, this.subtotal - this.discountAmount); },
        get change() { return Math.max(0, (Number(this.given) || 0) - this.total); },

        fmt,
    };
};

/* Stollar taxtasi: holat va nom bo'yicha filtr — serverga bormaydi. */
window.mBoard = function (places) {
    return {
        places: places || [],
        f: 'all',
        q: '',
        show(p) {
            const q = this.q.trim().toLowerCase();
            return (this.f === 'all' || p.st === this.f) && (!q || p.name.toLowerCase().includes(q));
        },
        get visible() { return this.places.filter((p) => this.show(p)).length; },
    };
};
