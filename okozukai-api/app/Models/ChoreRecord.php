<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\hasMany;

// お手伝い実績
class ChoreRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chore_id',
        'registered_by',
        'reward_amount',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'reward_amount' => 'integer',
            'performed_at' => 'date',
        ];
    }

    // ユーザー情報
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // お手伝い報酬設定
    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    // お手伝い実績登録を行なった保護者
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    // 収入・収支
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
