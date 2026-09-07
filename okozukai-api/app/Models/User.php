<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
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
        'family_id', // ユーザに紐づく家族テーブル
        'name', // ユーザ名
        'email', // 保護者のメールアドレス
        'login_id', // お子様のログインID
        'password', // パスワード
        'role', // parent or child
        'profile_image', // プロフィール画像
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

    // パスワード再設定のメールの件名と文章
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
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

    // お手伝いの実績
    public function choreRecords(): HasMany
    {
        return $this->hasMany(ChoreRecord::class);
    }

    // お手伝いの実績登録をした保護者
    public function registeredChoreRecords(): HasMany
    {
        return $this->hasMany(ChoreRecord::class, 'registered_by');
    }

    // 保護者がおこづかい入金をしたお子様の最新データを取得
    public function allowance(): HasOne
    {
        return $this->hasOne(Allowance::class)->latestOfMany();
    }

    // お子様に対してのおこづかい入金
    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class);
    }

    // お子様の収入と収支に関するテーブル
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // お子様の収支を報告した保護者ユーザ
    public function createdTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    // 貯金の目標
    public function savingGoal(): HasOne
    {
        return $this->hasOne(SavingGoal::class);
    }

    // お手伝い設定を行なった保護者
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
