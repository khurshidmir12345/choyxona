{{--
    Rasm yuklash maydoni (Livewire + Alpine).

    Nega oddiy <input type="file" wire:model> emas:
    telefon/skrinshot PNG lari 3–8 MB bo'ladi, PHP esa sukut bo'yicha 2 MB
    dan kattasini qabul qilmaydi — yuklash indamay xato berardi. Endi rasm
    brauzerda kichraytirilib (eng uzun tomoni 1280px, JPEG) yuboriladi,
    natijada odatda 100–300 KB chiqadi. Xatolar o'zbekcha va aniq.

    Misol: <x-image-upload model="image" :preview="$currentImage" />
--}}
@props([
    'model',                 // Livewire xususiyati nomi
    'preview' => null,       // hozirgi rasm URL (tahrirlashda)
    'label' => 'Rasm',
    'hint' => 'JPG, PNG yoki WebP. Avtomatik kichraytiriladi.',
    'size' => 96,            // ko'rish oynasi o'lchami, px
    'shape' => 'rounded',    // rounded | circle
])

<div class="img-upload-wrap">
<div class="img-upload {{ $shape === 'circle' ? 'is-circle' : '' }}"
     x-data="imageUpload({ model: @js($model), preview: @js($preview) })"
     wire:ignore>
    <input type="file" accept="image/*" class="d-none" x-ref="input" x-on:change="pick($event.target.files[0]); $event.target.value = ''">

    <button type="button" class="img-upload-box" style="width: {{ $size }}px; height: {{ $size }}px"
            :class="{ 'is-drag': dragging, 'has-image': !!preview, 'is-busy': uploading }"
            x-on:click="$refs.input.click()"
            x-on:dragover.prevent="dragging = true"
            x-on:dragleave.prevent="dragging = false"
            x-on:drop.prevent="dragging = false; pick($event.dataTransfer.files[0])"
            title="Rasm tanlash">
        <template x-if="preview">
            <img :src="preview" alt="">
        </template>
        <template x-if="!preview">
            <span class="img-upload-empty">
                <i class="mdi mdi-camera-plus-outline"></i>
            </span>
        </template>
        <span class="img-upload-progress" x-show="uploading" x-cloak>
            <span :style="`width: ${progress}%`"></span>
        </span>
        <span class="img-upload-check" x-show="done && !uploading" x-cloak><i class="mdi mdi-check-bold"></i></span>
    </button>

    <div class="img-upload-side">
        <label class="form-label">{{ $label }}</label>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-inverse-primary btn-sm" x-on:click="$refs.input.click()"
                    :disabled="uploading">
                <i class="mdi mdi-image-plus me-1"></i>
                <span x-text="preview ? 'Almashtirish' : 'Rasm tanlash'"></span>
            </button>
            <button type="button" class="btn btn-inverse-secondary btn-sm" x-show="preview" x-cloak
                    x-on:click="clear()" :disabled="uploading">
                <i class="mdi mdi-close"></i>
            </button>
        </div>
        <p class="img-upload-hint" x-show="!error && !uploading">{{ $hint }}</p>
        <p class="img-upload-hint" x-show="uploading" x-cloak>
            Yuklanmoqda... <span x-text="progress + '%'"></span>
        </p>
        <p class="img-upload-error" x-show="error" x-text="error" x-cloak></p>
    </div>
</div>
{{-- Server xatosi wire:ignore dan tashqarida turishi shart, aks holda yangilanmaydi --}}
@error($model) <p class="img-upload-error mt-1">{{ $message }}</p> @enderror
</div>
