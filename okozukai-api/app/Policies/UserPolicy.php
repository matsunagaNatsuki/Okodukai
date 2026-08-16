<?php

namespace App\Policies;

use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\User;

class UserPolicy
{
    public function viewFamilyChild(User $parent, User $child): bool
    {
        // ログインしている保護者が、指定された子どもの情報を閲覧してよいか
        return $parent->role === 'parent'
            && $parent->family_id !== null
            && $child->role === 'child'
            && $parent->family_id === $child->family_id;
    }

    public function manageFamilyChore(User $parent, Chore $chore): bool
    {
        // ログイン中の保護者が、お手伝い報酬設定を操作してもよいか
        return $parent->role === 'parent'
            && $parent->family_id !== null
            && $parent->family_id === $chore->family_id;
    }

    public function manageChoreRecord(User $parent, ChoreRecord $choreRecord): bool
    {
        // ログイン中の保護者が、お手伝い実績を操作してよいか
        return $parent->role === 'parent'
            && $parent->family_id !== null
            && $choreRecord->user?->role === 'child'
            && $parent->family_id === $choreRecord->user?->family_id;
    }

    public function manageFamilyUser(User $parent, User $account): bool
    {
        // ログイン中の保護者が家族アカウントを操作してもよいか
        return $parent->role === 'parent'
            && $parent->family_id !== null
            && $parent->family_id === $account->family_id;
    }

    public function deleteFamilyUser(User $parent, User $account): bool
    {
        // 同じ家族のアカウントかつ、本人・家族オーナー以外の場合のみ削除を許可
        return $this->manageFamilyUser($parent, $account)
            && $parent->id !== $account->id
            && $parent->family?->owner_user_id !== $account->id;
    }
}
