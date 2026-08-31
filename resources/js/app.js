import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

/**
 * Ilova hozir Alpine'da ishlaydi. Ilgari yuklanadigan jQuery, Bootstrap JS,
 * DataTables, datepicker va beshta ikonka shrifti olib tashlandi —
 * ular birgalikda har bir sahifada ~1.5 MB edi.
 */
window.Alpine = Alpine;
Alpine.plugin(collapse);

/** Livewire hodisalaridan keladigan qisqa bildirishnomalar. */
Alpine.store('toasts', {
    items: [],
    seq: 0,

    push(message, type = 'success') {
        const id = ++this.seq;
        this.items.push({ id, message, type });
        setTimeout(() => this.dismiss(id), 3500);
    },

    dismiss(id) {
        this.items = this.items.filter((toast) => toast.id !== id);
    },
});

/** Yon menyuning yig'ilgan holati qayta yuklashdan keyin ham saqlanadi. */
Alpine.store('sidebar', {
    open: false,

    collapsed: localStorage.getItem('sidebar-collapsed') === '1',

    toggle() {
        this.open = !this.open;
    },

    toggleCollapsed() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0');
    },
});

document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (event) => {
        const payload = Array.isArray(event) ? event[0] : event;
        Alpine.store('toasts').push(payload.message, payload.type ?? 'success');
    });
});

Alpine.start();

/**
 * Summani "1 250 000" ko'rinishida ko'rsatish uchun kichik yordamchi —
 * POS ekranidagi tez kiritish maydonlarida ishlatiladi.
 */
window.formatSom = (value) => {
    const digits = String(value ?? '').replace(/\D/g, '');

    return digits ? Number(digits).toLocaleString('ru-RU').replace(/ /g, ' ') : '';
};
