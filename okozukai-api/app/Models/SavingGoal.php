<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// 貯金の目標
class SavingGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // お子様ユーザー
        'item_name', // お子様が欲しいもの
        'target_amount', // 目標金額
        'is_completed', // 目標金額を達成したか
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'integer',
            'is_completed' => 'boolean',
        ];
    }

    // ユーザー情報
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
