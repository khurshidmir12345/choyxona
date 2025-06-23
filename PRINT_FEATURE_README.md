# Chek Bosib Chiqarish Xususiyati

## Tavsif

Buyurtma statusi "Done" (tugatilgan) bo'lganda, chek shaklida bosib chiqarish imkoniyati qo'shildi.

## Qo'shilgan Fayllar

### 1. Livewire Komponent

-   `app/Livewire/Admin/Orders/OrderCompleted.php` - Chek sahifasi uchun Livewire komponenti

### 2. Blade View

-   `resources/views/livewire/admin/orders/order-completed.blade.php` - Chek ko'rinishi
-   `resources/views/layouts/print.blade.php` - Bosib chiqarish uchun maxsus layout

### 3. Route

-   `routes/web.php` ga qo'shilgan: `Route::get('/order/{id}/print', OrderCompleted::class)->name('admin.orders.print');`

### 4. Yangilangan Fayl

-   `resources/views/livewire/admin/orders/index-livewire.blade.php` - Print tugmasi qo'shildi

## Xususiyatlar

### Chek Ma'lumotlari:

-   Choyxona nomi
-   Buyurtma raqami
-   Sana va vaqt
-   Buyurtma turi (Yetkazib berish/Olib ketish/Choyxonada)
-   Mahsulotlar ro'yxati (nomi, soni, narxi)
-   Chegirma ma'lumotlari
-   Umumiy summa
-   Xodim ma'lumotlari
-   Kassir ma'lumotlari

### Bosib Chiqarish:

-   `Ctrl + P` yoki tugma orqali bosib chiqarish
-   Print-optimized CSS
-   Mobil va oddiy printer uchun moslashgan
-   Bosib chiqarishda tugma yashirinadi

## Foydalanish

1. Buyurtmalar ro'yxatida "Done" statusidagi buyurtmalar uchun "🖨️ Chek" tugmasi ko'rinadi
2. Tugmani bosib yangi oynada chek ochiladi
3. `Ctrl + P` yoki "Bosib chiqarish" tugmasini bosib chekni chop eting

## Xavfsizlik

-   Faqat "Done" statusidagi buyurtmalar uchun chek ochiladi
-   Foydalanuvchi faqat o'z kompaniyasining buyurtmalarini ko'ra oladi
-   Authentication middleware qo'llanilgan

## Texnik Ma'lumotlar

-   Laravel Livewire 3.x
-   Print-optimized CSS
-   Responsive design
-   Browser print API
