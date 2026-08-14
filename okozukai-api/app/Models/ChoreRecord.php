<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
