<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Oflayn kassada yaratilgan sotuvning brauzerdagi UUID'si.
 * Sinxronlash takrorlansa ham bitta buyurtma ikki marta yozilmaydi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('offline_uuid', 36)->nullable()->after('delivery_address');
            $table->unique('offline_uuid', 'orders_offline_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_offline_uuid_unique');
            $table->dropColumn('offline_uuid');
        });
    }
};
