<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'employee']);
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can view their subordinates' requests
        if ($user->id === $leaveRequest->user->reporting_to_id) {
            return true;
        }

        // Employees can view their own
        return $user->id === $leaveRequest->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'employee']);
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can update/approve subordinates' requests
        if ($user->id === $leaveRequest->user->reporting_to_id && $leaveRequest->status === 'pending') {
            return true;
        }

        // Employees can only edit their pending requests
        return $user->id === $leaveRequest->user_id && $leaveRequest->status === 'pending';
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Employees can delete (cancel) their own pending requests
        return $user->id === $leaveRequest->user_id && $leaveRequest->status === 'pending';
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can approve subordinates' requests (but not their own)
        return $user->id === $leaveRequest->user->reporting_to_id && $user->id !== $leaveRequest->user_id;
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can reject subordinates' requests (but not their own)
        return $user->id === $leaveRequest->user->reporting_to_id && $user->id !== $leaveRequest->user_id;
    }
}
