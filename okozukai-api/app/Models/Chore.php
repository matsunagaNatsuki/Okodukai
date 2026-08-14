<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chore extends Model
{
    use HasFactory, SoftDeletes;

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

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function choreRecords(): HasMany
    {
        return $this->hasMany(ChoreRecord::class);
    }
}
