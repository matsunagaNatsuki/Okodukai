<?php

namespace App\Services;

use App\Models\Family;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ParentRegistrationService
{
    public function register(array $attributes): array
    {
        return DB::transaction(function () use ($attributes): array {
            $parent = User::create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => Hash::make($attributes['password']),
                'role' => 'parent',
            ]);
            $familyCode = $this->generateUniqueFamilyCode();
            $family = Family::create(['owner_user_id' => $parent->id, 'family_code' => $familyCode]);
            $parent->update(['family_id' => $family->id]);

            return [$parent, $familyCode];
        });
    }

    private function generateUniqueFamilyCode(): string
    {
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $code = str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT);
            if (! Family::withTrashed()->where('family_code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('家族コードを生成できませんでした。');
    }
}
