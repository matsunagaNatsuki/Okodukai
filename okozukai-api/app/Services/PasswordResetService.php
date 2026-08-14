<?php

namespace App\Services;

use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function issueCode(string $email): PasswordResetCode
    {
        $owner = User::query()
            ->where('email', $email)
            ->where('role', 'parent')
            ->whereHas('ownedFamily')
            ->first();

        if ($owner === null) {
            throw ValidationException::withMessages([
                'email' => '代表保護者のメールアドレスが見つかりません。',
            ]);
        }

        $resetCode = DB::transaction(function () use ($owner): PasswordResetCode {
            $owner->passwordResetCodes()
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->update(['expires_at' => now()]);

            return $owner->passwordResetCodes()->create([
                'code' => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                'expires_at' => now()->addMinutes(10),
            ]);
        });

        Mail::to($owner->email)->send(new PasswordResetCodeMail($resetCode->code));

        return $resetCode;
    }

    public function verifyCode(int $ownerId, string $code): PasswordResetCode
    {
        $resetCode = PasswordResetCode::query()
            ->where('user_id', $ownerId)
            ->where('code', $code)
            ->latest('id')
            ->first();

        if ($resetCode === null) {
            throw ValidationException::withMessages(['code' => '確認コードが正しくありません。']);
        }

        if ($resetCode->used_at !== null) {
            throw ValidationException::withMessages(['code' => 'この確認コードは使用済みです。']);
        }

        if ($resetCode->expires_at->isPast()) {
            throw ValidationException::withMessages(['code' => '確認コードの有効期限が切れています。もう一度発行してください。']);
        }

        return $resetCode;
    }

    public function familyUsers(PasswordResetCode $resetCode)
    {
        return $resetCode->user->ownedFamily->users()
            ->orderByRaw("CASE WHEN role = 'parent' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
    }

    public function resetPassword(PasswordResetCode $resetCode, int $targetUserId, string $password): User
    {
        return DB::transaction(function () use ($resetCode, $targetUserId, $password): User {
            $lockedCode = PasswordResetCode::query()->lockForUpdate()->findOrFail($resetCode->id);

            if ($lockedCode->used_at !== null || $lockedCode->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'password' => '確認コードが使用済み、または有効期限切れです。最初からやり直してください。',
                ]);
            }

            $family = $lockedCode->user->ownedFamily;
            $target = $family?->users()->whereKey($targetUserId)->lockForUpdate()->first();

            if ($target === null) {
                throw ValidationException::withMessages(['user_id' => '対象ユーザーを確認できません。']);
            }

            $target->update(['password' => Hash::make($password)]);
            $lockedCode->update(['used_at' => now()]);

            return $target;
        });
    }
}
