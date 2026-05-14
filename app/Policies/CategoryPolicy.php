<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdminUser();
    }

    public function create(User $user): bool
    {
        return $user->isAdminUser();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isAdminUser();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->isAdminUser();
    }
}
