<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// お手伝いの設定
class Chore extends Model
{
    use  SoftDeletes;

    protected $fillable = [
        'family_id', // 家族テーブル
        'chore_name', // お手伝いの内容
        'reward_amount', // お手伝いの設定金額
        'created_by', // お手伝いの設定を行なったユーザ
    ];

    protected function casts(): array
    {
        return ['reward_amount' => 'integer'];
    }

    // お手伝いの設定に紐づく家族テーブル
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    // お手伝いの設定を行なった保護者ユーザー
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // お手伝いの実績
    public function choreRecords(): HasMany
    {
        return $this->hasMany(ChoreRecord::class);
    }
}
