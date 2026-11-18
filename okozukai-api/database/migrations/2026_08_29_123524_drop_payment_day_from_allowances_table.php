<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 入金日(payment_day)と支払済みか(is_active)のindexを削除
        Schema::table('allowances', function (Blueprint $table) {
            $table->dropIndex('allowances_user_id_is_active_index');
        });

        // allowancesテーブルに入金日(payment_day)と支払済みか(is_active)のカラムを削除
        Schema::table('allowances', function (Blueprint $table) {
            $table->dropColumn('payment_day');
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 再作成する際はunsignedTinyIntegerにロールバックする
        Schema::table('allowances', function (Blueprint $table) {
            $table->unsignedTinyInteger('payment_day');
            $table->unsignedTinyInteger('is_active');
        });

        // 一度削除したindexカラムをロールバック
        Schema::table('allowances', function (Blueprint $table) {
            $table->unsignedTinyInteger('payment_day');
            $table->unsignedTinyInteger('is_active');
        });
    }
};
