<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// お手伝い報酬設定
class Chore extends Model
{
    use  SoftDeletes;

    protected $fillable = [
        'family_id',
        'chore_name',
        'reward_amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return ['reward_amount' => 'integer'];
    }

    // 家族情報
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    // お手伝い報酬設定を行なったユーザー
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // お手伝い実績
    public function choreRecords(): HasMany
    {
        return $this->hasMany(ChoreRecord::class);
    }
}
