/*
 * Rasm yuklash (Alpine komponenti) — resources/views/components/image-upload.blade.php
 *
 * Rasm serverga yuborilishidan oldin brauzerda kichraytiriladi:
 * eng uzun tomoni MAX_SIDE px, JPEG sifat QUALITY. Shu tufayli 5–8 MB li
 * PNG skrinshotlar ham PHP ning 2 MB chegarasiga urilmaydi.
 */
(function () {
    const MAX_SIDE = 1280;
    const QUALITY = 0.86;
    const KEEP_UNDER = 350 * 1024;         // bundan kichik rasm o'zgartirilmaydi
    const HARD_LIMIT = 25 * 1024 * 1024;   // umuman katta fayllarni ochib o'tirmaymiz

    function loadImage(file) {
        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('decode')); };
            img.src = url;
        });
    }

    function toBlob(canvas) {
        return new Promise((resolve, reject) => {
            canvas.toBlob((b) => b ? resolve(b) : reject(new Error('encode')), 'image/jpeg', QUALITY);
        });
    }

    async function shrink(file) {
        const type = (file.type || '').toLowerCase();
        const simple = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(type);

        if (simple && file.size <= KEEP_UNDER) {
            return file;
        }

        const img = await loadImage(file);
        const scale = Math.min(1, MAX_SIDE / Math.max(img.naturalWidth, img.naturalHeight));
        const w = Math.max(1, Math.round(img.naturalWidth * scale));
        const h = Math.max(1, Math.round(img.naturalHeight * scale));

        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';          // PNG shaffofligi qora bo'lib qolmasin
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);

        const blob = await toBlob(canvas);
        const base = (file.name || 'rasm').replace(/\.[^.]+$/, '');

        return new File([blob], base + '.jpg', { type: 'image/jpeg' });
    }

    window.imageUpload = function (opts) {
        return {
            model: opts.model,
            preview: opts.preview || null,
            original: opts.preview || null,
            uploading: false,
            dragging: false,
            done: false,
            progress: 0,
            error: null,

            async pick(file) {
                if (!file) return;
                this.error = null;
                this.done = false;

                if (!/^image\//.test(file.type || '')) {
                    this.error = 'Faqat rasm fayli tanlang (JPG, PNG, WebP).';
                    return;
                }
                if (file.size > HARD_LIMIT) {
                    this.error = 'Rasm juda katta (25 MB dan oshmasin).';
                    return;
                }

                let prepared;
                try {
                    prepared = await shrink(file);
                } catch (e) {
                    this.error = 'Bu rasmni ochib bo\'lmadi. Boshqa formatda (JPG/PNG) urinib ko\'ring.';
                    return;
                }

                this.preview = URL.createObjectURL(prepared);
                this.uploading = true;
                this.progress = 0;

                this.$wire.upload(
                    this.model,
                    prepared,
                    () => { this.uploading = false; this.done = true; },
                    () => {
                        this.uploading = false;
                        this.preview = this.original;
                        this.error = 'Yuklab bo\'lmadi. Internetni tekshiring yoki kichikroq rasm tanlang.';
                    },
                    (e) => { this.progress = e.detail.progress; }
                );
            },

            clear() {
                this.error = null;
                this.done = false;
                this.preview = this.original;
                this.$wire.$set(this.model, null);
            },
        };
    };
})();
