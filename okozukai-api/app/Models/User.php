<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'family_id',
        'name',
        'email',
        'login_id',
        'password',
        'role',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function ownedFamily(): HasOne
    {
        return $this->hasOne(Family::class, 'owner_user_id');
    }

    public function choreRecords(): HasMany
    {
        return $this->hasMany(ChoreRecord::class);
    }

    public function registeredChoreRecords(): HasMany
    {
        return $this->hasMany(ChoreRecord::class, 'registered_by');
    }

    public function allowance(): HasOne
    {
        return $this->hasOne(Allowance::class)->latestOfMany();
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function createdTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    public function savingGoal(): HasOne
    {
        return $this->hasOne(SavingGoal::class);
    }

    public function createdChores(): HasMany
    {
        return $this->hasMany(Chore::class, 'created_by');
    }

    public function passwordResetCodes(): HasMany
    {
        return $this->hasMany(PasswordResetCode::class);
    }
}
