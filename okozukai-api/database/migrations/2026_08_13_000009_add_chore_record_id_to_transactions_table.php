<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('chore_record_id')
                ->nullable()
                ->after('id')
                ->unique()
                ->constrained('chore_records')
                ->nullOnDelete();
        });

        DB::table('chore_records')
            ->orderBy('id')
            ->each(function (object $record): void {
                $chore = DB::table('chores')->where('id', $record->chore_id)->first();

                if ($chore === null) {
                    return;
                }

                $candidates = DB::table('transactions')
                    ->whereNull('chore_record_id')
                    ->whereNull('deleted_at')
                    ->where('user_id', $record->user_id)
                    ->where('type', 'income')
                    ->where('category', 'chore')
                    ->where('amount', $record->reward_amount)
                    ->where('title', $chore->chore_name)
                    ->where('created_by', $record->registered_by)
                    ->where('created_at', $record->created_at)
                    ->limit(2)
                    ->pluck('id');

                if ($candidates->count() === 1) {
                    DB::table('transactions')
                        ->where('id', $candidates->first())
                        ->update(['chore_record_id' => $record->id]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chore_record_id');
        });
    }
};
