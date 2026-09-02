<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Biznes turi: choyxona/kafe/restoran yoki oddiy do'kon (donali savdo).
 * Bor kompaniyalar choyxona rejimida qoladi; yangilari kirganda tanlaydi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('business_type', 20)->nullable()->after('name');
        });

        DB::table('companies')->whereNull('business_type')->update(['business_type' => 'cafe']);
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('business_type');
        });
    }
};
