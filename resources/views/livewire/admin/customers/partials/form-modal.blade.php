{{-- Mijoz formasi: ro'yxat va mijoz sahifasida bir xil. --}}
<x-modal :title="$title" icon="mdi-account-outline" subtitle="Telefon bo'yicha takrorlanmaydi" close="closeForm">
    <form wire:submit="save">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Ismi</label>
                <input type="text" wire:model="name" autofocus
                       class="form-control @error('name') is-invalid @enderror" placeholder="Masalan: Akmal aka">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Telefon</label>
                <input type="tel" wire:model="phone"
                       class="form-control tabular @error('phone') is-invalid @enderror" placeholder="+998 90 123 45 67">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Asosiy manzil</label>
                <input type="text" wire:model="address" class="form-control" placeholder="Ko'cha, uy, mo'ljal">
                <div class="form-text">Yetkazib berishda avtomatik taklif qilinadi.</div>
            </div>
            <div>
                <label class="form-label">Izoh</label>
                <input type="text" wire:model="note" class="form-control" placeholder="Masalan: achchiq yoqtirmaydi">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-inverse-secondary" wire:click="closeForm">Bekor qilish</button>
            <button type="submit" class="btn btn-primary"><i class="mdi mdi-check me-1"></i> Saqlash</button>
        </div>
    </form>
</x-modal>
