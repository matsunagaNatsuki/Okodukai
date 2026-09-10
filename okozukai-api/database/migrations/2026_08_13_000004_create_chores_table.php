<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chores', function (Blueprint $table) {
            $table->id();
            // お手伝いの設定に紐づく家族テーブル
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('chore_name', 100);
            $table->unsignedInteger('reward_amount');
            // // お手伝いの設定を行なった保護者ユーザ
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chores');
    }
};
