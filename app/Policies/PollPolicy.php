<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Poll;
use Illuminate\Auth\Access\HandlesAuthorization;

class PollPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Poll');
    }

    public function view(AuthUser $authUser, Poll $poll): bool
    {
        return $authUser->can('View:Poll');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Poll');
    }

    public function update(AuthUser $authUser, Poll $poll): bool
    {
        return $authUser->can('Update:Poll');
    }

    public function delete(AuthUser $authUser, Poll $poll): bool
    {
        return $authUser->can('Delete:Poll');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Poll');
    }

    public function restore(AuthUser $authUser, Poll $poll): bool
    {
        return $authUser->can('Restore:Poll');
    }

    public function forceDelete(AuthUser $authUser, Poll $poll): bool
    {
        return $authUser->can('ForceDelete:Poll');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Poll');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Poll');
    }

    public function replicate(AuthUser $authUser, Poll $poll): bool
    {
        return $authUser->can('Replicate:Poll');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Poll');
    }

}