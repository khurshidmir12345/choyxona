<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tez-tez ishlaydigan so'rovlar uchun indekslar:
 *  - menyu nom bo'yicha tartiblanadi (company_id, name)
 *  - kategoriyalar nom bo'yicha (company_id, name)
 *  - "bugun yopilgan" ro'yxati (company_id, status, updated_at)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['company_id', 'name'], 'products_company_name_idx');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->index(['company_id', 'name'], 'product_categories_company_name_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['company_id', 'status', 'updated_at'], 'orders_company_status_updated_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_company_name_idx');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex('product_categories_company_name_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_company_status_updated_idx');
        });
    }
};
