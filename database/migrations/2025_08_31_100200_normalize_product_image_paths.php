<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ilgari rasm yo'li to'liq URL sifatida saqlangan (asset('storage/...')),
 * shuning uchun domen o'zgarsa hamma rasm siniq bo'lardi.
 * Bu yerda faqat nisbiy yo'l qoldiramiz: "products/abc.jpg".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($products) {
                foreach ($products as $product) {
                    $normalized = $this->normalize($product->image);

                    if ($normalized !== $product->image) {
                        DB::table('products')->where('id', $product->id)->update(['image' => $normalized]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Yo'lni orqaga qaytarish shart emas: imageUrl() ikkala shaklni ham tushunadi.
    }

    private function normalize(string $image): string
    {
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            $image = (string) parse_url($image, PHP_URL_PATH);
        }

        $image = ltrim($image, '/');

        if (str_starts_with($image, 'storage/')) {
            $image = substr($image, strlen('storage/'));
        }

        return $image;
    }
};
