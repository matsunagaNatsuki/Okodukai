<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// 保護者新規登録の際の２要素認証の入力情報
class ParentRegistrationVerification extends Model
{
    protected $fillable = [
        'token',
        'name',
        'email',
        'password',
        'code', // 4桁の確認コード
        'expires_at', // 有効期限
        'attempts', // 試行回数
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
