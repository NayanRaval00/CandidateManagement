<?php

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, LeaveType $leaveType): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, LeaveType $leaveType): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, LeaveType $leaveType): bool
    {
        return $user->hasRole('admin');
    }
}
