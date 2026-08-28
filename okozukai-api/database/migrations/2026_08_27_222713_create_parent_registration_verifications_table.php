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
        Schema::create('parent_registration_verifications', function (Blueprint $table) {
            $table->id();

            $table->uuid('token')->unique();

            $table->string('name', 255);
            $table->string('email', 255)->index();

            // 暗号化したパスワード
            $table->text('password');

            // Hash化した4桁コード
            $table->string('code');

            // コードの有効期限
            $table->timestamp('expires_at');

            // コード入力失敗回数
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_registration_verifications');
    }
};
