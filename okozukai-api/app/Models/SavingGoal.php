<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_name',
        'target_amount',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'integer',
            'is_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
