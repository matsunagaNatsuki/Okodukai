<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// お子様のおこづかい入金
class Allowance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', // おこづかい入金したお子様ユーザ
        'amount', // おこづかいの入金金額
        // 'payment_day',
        // 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            // 'payment_day' => 'integer',
            // 'is_active' => 'boolean',
        ];
    }

    // ユーザー情報
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
