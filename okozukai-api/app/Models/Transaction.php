<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// 収入・収支
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chore_record_id',
        'user_id',
        'type',
        'category',
        'amount',
        'transaction_date',
        'title',
        'created_by',
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

    // お手伝い実績登録
    public function choreRecord(): BelongsTo
    {
        return $this->belongsTo(ChoreRecord::class);
    }

    // 収支報告したユーザ
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
