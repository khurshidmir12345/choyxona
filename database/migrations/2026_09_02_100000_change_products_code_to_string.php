<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mahsulot kodi endi "MXS-10001" ko'rinishida: skaner uchun qat'iy, noyob
 * va id dan hosil qilinadi. Ilgari oddiy butun son bo'lib, qo'lda kiritilardi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->change();
        });

        // Bor mahsulotlarga yangi ko'rinishdagi kod beriladi.
        DB::table('products')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('products')
                        ->where('id', $row->id)
                        ->update(['code' => \App\Models\Product::codeFor((int) $row->id)]);
                }
            });
    }

    public function down(): void
    {
        DB::table('products')->update(['code' => DB::raw('NULL')]);

        Schema::table('products', function (Blueprint $table) {
            $table->integer('code')->nullable()->change();
        });
    }
};
