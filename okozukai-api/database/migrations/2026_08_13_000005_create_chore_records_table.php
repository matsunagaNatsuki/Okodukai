<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chore_records', function (Blueprint $table) {
            $table->id();
            // お手伝いを行なったお子様ユーザ
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // お手伝いの内容
            $table->foreignId('chore_id')->constrained()->restrictOnDelete();
            // お手伝いの実績登録をした保護者ユーザ
            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('reward_amount');
            $table->date('performed_at'); // 別のマイグレーションファイルで削除
            $table->timestamps();
            $table->index('user_id');
            $table->index(['user_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chore_records');
    }
};
