<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Додає прапорець адміністратора до таблиці users.
     */
    public function up(): void
    {
        // (укр.) Якщо колонки вже немає — додаємо
        if (!Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                // (укр.) Булеве поле is_admin з дефолтом false
                // Можеш прибрати ->after(...) якщо структура інша
                $table->boolean('is_admin')->default(false)->after('email_verified_at');
            });
        }
    }

    /**
     * Відкочує зміни (видаляє поле is_admin).
     */
    public function down(): void
    {
        // Видаляємо колонку лише якщо вона існує
        if (Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }
    }
};
