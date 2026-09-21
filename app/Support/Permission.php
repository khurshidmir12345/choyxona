<?php

namespace App\Support;

/**
 * Bo'limlarga kirish ruxsatlari. Har bir kalit sidebar'dagi bitta bo'lim
 * va marshrutlardagi `perm:<kalit>` middleware'iga mos keladi.
 *
 * Kompaniya egasi hamma narsaga kiradi; xodimning ruxsatlari lavozimidan
 * (Role.permissions) olinadi.
 */
class Permission
{
    public const DASHBOARD = 'dashboard';

    public const HALL = 'hall';

    public const QUICK_SALE = 'quick_sale';

    public const ORDERS = 'orders';

    public const CUSTOMERS = 'customers';

    public const PRODUCTS = 'products';

    public const STOCK = 'stock';

    public const PLACES = 'places';

    public const EXPENSES = 'expenses';

    public const STAFF = 'staff';

    public const SETTINGS = 'settings';

    /**
     * Ruxsat → [nomi, izohi, ikonka]. Tartib — lavozim formasidagi tartib.
     *
     * @return array<string, array{label:string, hint:string, icon:string}>
     */
    public static function all(): array
    {
        return [
            self::DASHBOARD => ['label' => 'Bosh sahifa', 'hint' => 'Savdo ko\'rsatkichlari va statistika', 'icon' => 'mdi-view-dashboard-outline'],
            self::HALL => ['label' => 'Zal (stollar)', 'hint' => 'Stollarda buyurtma ochish va yopish', 'icon' => 'mdi-sofa-outline'],
            self::QUICK_SALE => ['label' => 'Tez sotuv', 'hint' => 'Olib ketish va yetkazib berish kassasi', 'icon' => 'mdi-cash-register'],
            self::ORDERS => ['label' => 'Sotuv tarixi', 'hint' => 'Barcha buyurtmalar va arxiv', 'icon' => 'mdi-history'],
            self::CUSTOMERS => ['label' => 'Mijozlar', 'hint' => 'Mijozlar bazasi', 'icon' => 'mdi-account-group-outline'],
            self::PRODUCTS => ['label' => 'Mahsulotlar', 'hint' => 'Mahsulotlar va kategoriyalar', 'icon' => 'mdi-food-outline'],
            self::STOCK => ['label' => 'Kirim / chiqim', 'hint' => 'Ombor harakatlari', 'icon' => 'mdi-swap-vertical'],
            self::PLACES => ['label' => 'Joylar', 'hint' => 'Stollar va xonalarni boshqarish', 'icon' => 'mdi-table-furniture'],
            self::EXPENSES => ['label' => 'Xarajatlar', 'hint' => 'Chiqimlar va ularning kategoriyalari', 'icon' => 'mdi-wallet-outline'],
            self::STAFF => ['label' => 'Xodimlar', 'hint' => 'Xodimlar va lavozimlar', 'icon' => 'mdi-badge-account-horizontal-outline'],
            self::SETTINGS => ['label' => 'Profil va kompaniya', 'hint' => 'Kompaniya ma\'lumotlari, parol, biznes turi', 'icon' => 'mdi-account-cog-outline'],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Standart lavozimlar. Har bir kompaniyada avtomatik yaratiladi;
     * ruxsatlarini ega o'zgartira oladi, lekin o'chira olmaydi.
     *
     * @return array<string, array{name:string, permissions:list<string>}>
     */
    public static function defaultRoles(): array
    {
        return [
            'waiter' => [
                'name' => 'Ofitsant',
                'permissions' => [self::HALL],
            ],
            'cashier' => [
                'name' => 'Kassir',
                'permissions' => [
                    self::DASHBOARD, self::HALL, self::QUICK_SALE, self::ORDERS, self::CUSTOMERS,
                    self::PRODUCTS, self::STOCK, self::PLACES, self::EXPENSES,
                ],
            ],
            'manager' => [
                'name' => 'Menejer',
                'permissions' => self::keys(),
            ],
        ];
    }

    /**
     * Foydalanuvchi kirganda ochiladigan birinchi bo'lim — ruxsatlar
     * tartibida. Zal faqat kafe rejimida.
     *
     * @return array<string, string> ruxsat => marshrut nomi
     */
    public static function homeRoutes(bool $hasHall): array
    {
        return array_filter([
            self::DASHBOARD => 'dashboard',
            self::HALL => $hasHall ? 'cafe.create' : null,
            self::QUICK_SALE => 'orders.create',
            self::ORDERS => 'orders.index',
            self::CUSTOMERS => 'customers.index',
            self::PRODUCTS => 'products.index',
            self::STOCK => 'product-stock.index',
            self::PLACES => $hasHall ? 'places.index' : null,
            self::EXPENSES => 'expenses.index',
            self::STAFF => 'employees.index',
            self::SETTINGS => 'admin.profile',
        ]);
    }
}
