<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ro'yxat va hisobot so'rovlari doim company_id + sana/status bo'yicha
 * filtrlanadi. Bu indekslar shu so'rovlarni to'liq skanerdan qutqaradi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['company_id', 'status', 'created_at'], 'orders_company_status_created_idx');
            $table->index(['company_id', 'type'], 'orders_company_type_idx');
            $table->index(['place_id', 'status'], 'orders_place_status_idx');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->index(['order_id'], 'order_details_order_idx');
            $table->index(['product_id'], 'order_details_product_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['company_id', 'category_id'], 'products_company_category_idx');
            $table->index(['company_id', 'code'], 'products_company_code_idx');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->index(['company_id', 'product_id'], 'product_stocks_company_product_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['company_id', 'status', 'expense_date'], 'expenses_company_status_date_idx');
        });

        Schema::table('places', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'places_company_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_company_status_created_idx');
            $table->dropIndex('orders_company_type_idx');
            $table->dropIndex('orders_place_status_idx');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex('order_details_order_idx');
            $table->dropIndex('order_details_product_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_company_category_idx');
            $table->dropIndex('products_company_code_idx');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropIndex('product_stocks_company_product_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_company_status_date_idx');
        });

        Schema::table('places', function (Blueprint $table) {
            $table->dropIndex('places_company_status_idx');
        });
    }
};
