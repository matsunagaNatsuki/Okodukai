<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Database\Eloquent\Relations\hasMany;

// お手伝いの実績
class ChoreRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // お手伝いを行なったお子様ユーザ
        'chore_id', // お手伝いの内容
        'registered_by', // お手伝い登録をした保護者ユーザ
        'reward_amount', // お手伝いの金額設定
        // 'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'reward_amount' => 'integer',
            // 'performed_at' => 'date',
        ];
    }

    // ユーザー情報
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // お手伝い設定
    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    // お手伝いの実績登録を行なった保護者
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    // お子様の収入と収支に関するテーブル
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
