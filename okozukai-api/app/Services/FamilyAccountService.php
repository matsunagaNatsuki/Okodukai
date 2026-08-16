<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FamilyAccountService
{
    // 家族アカウントを新規作成
    public function create(User $parent, array $attributes, string $role): User
    {
        return User::create([
            'family_id' => $parent->family_id,
            'name' => $attributes['name'],
            'email' => $role === 'parent' ? $attributes['email'] : null,
            'login_id' => $attributes['login_id'],
            'password' => Hash::make($attributes['password']),
            'role' => $role,
        ]);
    }

    // 家族アカウントを編集
    public function update(User $account, array $attributes): void
    {
        $values = [
            'name' => $attributes['name'],
            'email' => $account->role === 'parent' ? $attributes['email'] : null,
            'login_id' => $attributes['login_id'],
        ];

        if (filled($attributes['password'] ?? null)) {
            $values['password'] = Hash::make($attributes['password']);
        }

        $account->update($values);
    }
}
