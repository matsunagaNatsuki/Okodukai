<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// 家族テーブル
class Family extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_user_id', // 家族代表の保護者ユーザー
        'family_code', // 8桁の家族アカウント番号
    ];

    // 家族代表の保護者ユーザー
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    // ユーザー情報
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // お手伝いの設定
    public function chores(): HasMany
    {
        return $this->hasMany(Chore::class);
    }
}
