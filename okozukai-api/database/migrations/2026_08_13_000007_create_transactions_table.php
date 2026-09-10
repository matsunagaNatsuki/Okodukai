<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // 対象のお子様ユーザ
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']);
            /*  income = 収入 expense = 支出 */
            $table->enum('category', ['allowance', 'chore', 'expense', 'adjustment']);
            /*  allowance = おこづかい入金での収入
                chore = お手伝いで得た収入
                expense = お子様が使ったもの
                adjustment = 残高調整用 */
            $table->unsignedInteger('amount');
            $table->string('title');
            // 収支の申請をしたユーザ
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
