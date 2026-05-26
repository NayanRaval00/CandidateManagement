<?php

namespace App\Policies;

use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveBalancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->hasRole('admin');
    }
}
