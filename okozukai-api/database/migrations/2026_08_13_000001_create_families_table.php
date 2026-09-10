<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            // 家族代表の保護者ユーザー
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            // 8桁の家族アカウント番号
            $table->char('family_code', 8)->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
