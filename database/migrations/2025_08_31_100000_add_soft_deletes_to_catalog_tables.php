<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * products / product_categories / places uchun soft delete.
 *
 * Sababi: bu jadvallardagi qatorlar order_details va orders'ga
 * onDelete('cascade') bilan bog'langan. Ilgari bitta mahsulotni o'chirish
 * o'sha mahsulot sotilgan barcha buyurtma qatorlarini ham o'chirib
 * yuborardi — ya'ni savdo tarixi yo'qolardi.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['products', 'product_categories', 'places'] as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['products', 'product_categories', 'places'] as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropSoftDeletes();
                });
            }
        }
    }
};
