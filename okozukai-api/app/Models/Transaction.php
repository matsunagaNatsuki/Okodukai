<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// お子様の収支
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chore_record_id', // お手伝いの履歴
        'user_id', // お子様ユーザ情報
        'type', // 収入 or 支出
        'category',// 収支の種類
        'amount', // 金額
        'transaction_date', // おこづかい入金日時
        'title', // お子様の収支の内容
        'created_by', // 収支の申請をしたユーザ
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'transaction_date' => 'date',
        ];
    }

    // ユーザー情報
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // お手伝いの実績
    public function choreRecord(): BelongsTo
    {
        return $this->belongsTo(ChoreRecord::class);
    }

    // お子様の収支を報告した保護者ユーザ
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
