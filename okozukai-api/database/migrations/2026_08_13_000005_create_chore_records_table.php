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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chore_id')->constrained()->restrictOnDelete();
            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('reward_amount');
            $table->date('performed_at');
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
