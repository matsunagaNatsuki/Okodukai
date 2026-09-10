<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // おこづかい入金日時のカラムを追加
        Schema::table('transactions', function (Blueprint $table) {
            $table->date('transaction_date')->nullable()->after('amount')->index();
        });

        // 後付けでtransaction_dateカラムにcreated_atの日時を挿入
        DB::table('transactions')
            ->whereNull('transaction_date')
            ->update(['transaction_date' => DB::raw('DATE(created_at)')]);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_date');
        });
    }
};
