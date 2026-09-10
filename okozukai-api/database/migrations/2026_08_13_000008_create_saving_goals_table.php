<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_goals', function (Blueprint $table) {
            $table->id();
            // 目標設定を行なったお子様ユーザー
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('target_amount');
            // 目標金額を達成したか
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_goals');
    }
};
