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

// ユーザー情報
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

    // 家族テーブル
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    // 家族代表ユーザー
    public function ownedFamily(): HasOne
    {
        return $this->hasOne(Family::class, 'owner_user_id');
    }

    // お手伝い実績登録
    public function choreRecords(): HasMany
    {
        return $this->hasMany(ChoreRecord::class);
    }

    // お手伝いの実績登録をした保護者
    public function registeredChoreRecords(): HasMany
    {
        return $this->hasMany(ChoreRecord::class, 'registered_by');
    }

    // ユーザが定期おこづかい設定をした最新データを取得
    public function allowance(): HasOne
    {
        return $this->hasOne(Allowance::class)->latestOfMany();
    }

    // 前ユーザのお手伝い実績
    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class);
    }

    // 収入・支出データ
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // 収支報告したユーザ
    public function createdTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    // 貯金目標
    public function savingGoal(): HasOne
    {
        return $this->hasOne(SavingGoal::class);
    }

    // お手伝い報酬を行なった保護者
    public function createdChores(): HasMany
    {
        return $this->hasMany(Chore::class, 'created_by');
    }

    // パスワード再設定
    public function passwordResetCodes(): HasMany
    {
        return $this->hasMany(PasswordResetCode::class);
    }
}
