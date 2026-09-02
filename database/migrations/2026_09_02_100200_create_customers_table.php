<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mijozlar: doimiy xaridorlar, ularning manzillari va savdo tarixi.
 * Buyurtmaga mijoz va yetkazib berish manzili biriktiriladi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->string('phone', 32)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index(['company_id', 'name'], 'customers_company_name_idx');
            $table->index(['company_id', 'phone'], 'customers_company_phone_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('user_id');
            $table->string('delivery_address', 500)->nullable()->after('status');

            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->index(['customer_id', 'created_at'], 'orders_customer_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex('orders_customer_created_idx');
            $table->dropColumn(['customer_id', 'delivery_address']);
        });

        Schema::dropIfExists('customers');
    }
};
