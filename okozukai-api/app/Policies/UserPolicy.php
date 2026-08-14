<?php

namespace App\Policies;

use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\User;

class UserPolicy
{
    public function viewFamilyChild(User $parent, User $child): bool
    {
        return $parent->role === 'parent'
            && $parent->family_id !== null
            && $child->role === 'child'
            && $parent->family_id === $child->family_id;
    }

    public function manageFamilyChore(User $parent, Chore $chore): bool
    {
        return $parent->role === 'parent'
            && $parent->family_id !== null
            && $parent->family_id === $chore->family_id;
    }

    public function manageChoreRecord(User $parent, ChoreRecord $choreRecord): bool
    {
        return $parent->role === 'parent'
            && $parent->family_id !== null
            && $choreRecord->user?->role === 'child'
            && $parent->family_id === $choreRecord->user?->family_id;
    }

    public function manageFamilyUser(User $parent, User $account): bool
    {
        return $parent->role === 'parent'
            && $parent->family_id !== null
            && $parent->family_id === $account->family_id;
    }

    public function deleteFamilyUser(User $parent, User $account): bool
    {
        return $this->manageFamilyUser($parent, $account)
            && $parent->id !== $account->id
            && $parent->family?->owner_user_id !== $account->id;
    }
}
