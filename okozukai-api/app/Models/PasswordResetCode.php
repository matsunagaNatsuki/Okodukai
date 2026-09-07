<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// パスワード再設定
class PasswordResetCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // パスワード再設定したいユーザ
        'code', // 4桁の確認コード
        'expires_at', // 有効期限
        'used_at', // 使用した時間
    ];

    protected $hidden = ['code'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    // ユーザー情報
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
