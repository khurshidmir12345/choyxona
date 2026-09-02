<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zaxira harakatida KIM va NIMA UCHUN saqlanadi — "qoldiq nega o'zgardi"
 * degan savolga jurnal javob bera olishi kerak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('product_id');
            $table->string('note', 255)->nullable()->after('type');

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'note']);
        });
    }
};
