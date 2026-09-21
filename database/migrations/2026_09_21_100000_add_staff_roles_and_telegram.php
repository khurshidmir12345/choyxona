<?php

use App\Support\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Xodimlar bo'limi: lavozimlar kompaniyaga bog'lanadi va ruxsatlar ro'yxatini
 * saqlaydi; xodimni vaqtincha o'chirish (is_active) va Telegram orqali kirish.
 * Bot tokeni va webhook siri global sozlamalar jadvalida.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->index()->constrained()->nullOnDelete();
            $table->string('slug', 40)->nullable()->after('name');
            $table->json('permissions')->nullable()->after('slug');
            $table->boolean('is_default')->default(false)->after('permissions');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('type');
            $table->unsignedBigInteger('telegram_id')->nullable()->after('is_active')->index();
            $table->string('telegram_username', 64)->nullable()->after('telegram_id');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'key' => 'telegram_bot_token',
            'value' => '8971928694:AAFfGsn-_A9tUIalXIOlAOe7nE9_3onlmvg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mavjud kompaniyalar uchun standart lavozimlar.
        $companyIds = DB::table('companies')->whereNull('deleted_at')->pluck('id');
        $rows = [];

        foreach ($companyIds as $companyId) {
            foreach (Permission::defaultRoles() as $slug => $role) {
                $rows[] = [
                    'company_id' => $companyId,
                    'name' => $role['name'],
                    'slug' => $slug,
                    'permissions' => json_encode($role['permissions']),
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if ($rows) {
            DB::table('roles')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'telegram_id', 'telegram_username']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['slug', 'permissions', 'is_default']);
        });
    }
};
