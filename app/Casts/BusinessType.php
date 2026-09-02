<?php

namespace App\Casts;

/**
 * Tizim qaysi biznes uchun ishlayotgani. Kafe rejimida zal va joylar bor,
 * do'kon rejimida ular yo'q, so'zlar va ikonkalar universal.
 */
enum BusinessType: string implements \Filament\Support\Contracts\HasLabel
{
    case Cafe = 'cafe';
    case Retail = 'retail';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Cafe => 'Choyxona, kafe, restoran',
            self::Retail => 'Oddiy do\'kon (POS)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Cafe => 'Zal va stollar, ochiq hisoblar, yetkazib berish va olib ketish.',
            self::Retail => 'Donali savdo: telefon aksessuarlari, kiyim, xo\'jalik mollari va boshqalar.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cafe => 'mdi-tea-outline',
            self::Retail => 'mdi-storefront-outline',
        };
    }

    /** @return array<int, string> */
    public function features(): array
    {
        return match ($this) {
            self::Cafe => ['Zal: stol va so\'rilar', 'Ochiq hisoblar va chek', 'Yetkazib berish, olib ketish', 'Zaxira, xarajat, mijozlar'],
            self::Retail => ['Tez kassa va skaner', 'Do\'konda va yetkazib berish', 'Zaxira va kirim/chiqim', 'Mijozlar va xarajatlar'],
        };
    }

    public function hasHall(): bool
    {
        return $this === self::Cafe;
    }

    /**
     * Ekrandagi so'zlar va ikonkalar. Kalit — bitta joyda, ikkala rejim yonma-yon,
     * shunda farq darhol ko'rinadi.
     *
     * @return array<string, string>
     */
    public function terms(): array
    {
        $cafe = $this === self::Cafe;

        return [
            'brand' => $cafe ? 'Choyxona' : 'Savdo POS',
            'brand_icon' => $cafe ? 'mdi-tea-outline' : 'mdi-storefront-outline',
            'products_subtitle' => $cafe ? 'Menyu va narxlar' : 'Tovarlar va narxlar',
            'products_icon' => $cafe ? 'mdi-food-outline' : 'mdi-package-variant-closed',
            'products_all_icon' => $cafe ? 'mdi-food-fork-drink' : 'mdi-package-variant',
            'products_empty' => $cafe ? 'Menyuga birinchi mahsulotni qo\'shing.' : 'Birinchi mahsulotni qo\'shing.',
            'product_icon' => $cafe ? 'mdi-food-variant' : 'mdi-package-variant-closed',
            'product_placeholder' => $cafe ? 'Masalan: Ko\'k choy' : 'Masalan: Telefon g\'ilofi',
            'categories_subtitle' => $cafe ? 'Menyu bo\'limlari' : 'Tovar guruhlari',
            'category_subtitle' => $cafe ? 'Menyu bo\'limi' : 'Tovar guruhi',
            'category_placeholder' => $cafe ? 'Masalan: Issiq ichimliklar' : 'Masalan: Aksessuarlar',
            'quick_sale' => $cafe ? 'Tez sotuv' : 'Sotuv',
            'quick_sale_subtitle' => $cafe ? 'Yetkazib berish va olib ketish buyurtmalari' : 'Do\'kondagi va yetkazib beriladigan sotuvlar',
            'quick_sale_icon' => $cafe ? 'mdi-cart-outline' : 'mdi-cash-register',
            'takeaway' => $cafe ? 'Olib ketish' : 'Do\'konda',
            'takeaway_hint' => $cafe ? 'Mijoz o\'zi keladi' : 'Mijoz do\'kondan oladi',
            'takeaway_icon' => $cafe ? 'mdi-shopping-outline' : 'mdi-store-outline',
            'orders' => $cafe ? 'Buyurtmalar' : 'Sotuvlar',
            'orders_history' => $cafe ? 'Buyurtmalar tarixi' : 'Sotuvlar tarixi',
            'orders_empty' => $cafe ? 'Buyurtma topilmadi' : 'Sotuv topilmadi',
            'pos_empty_icon' => $cafe ? 'mdi-food-off' : 'mdi-package-variant-remove',
        ];
    }

    public function term(string $key): string
    {
        return $this->terms()[$key] ?? $key;
    }
}
