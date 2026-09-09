<?php

namespace App\Policies;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;

class ScholarshipApplicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScholarshipApplication $scholarshipApplication): bool
    {
        return $user->isAdmin() || $user->id === $scholarshipApplication->user_id;
    }

    /**
     * Determine whether the user can apply to the given scholarship.
     */
    public function create(User $user, Scholarship $scholarship): bool
    {
        if (! $user->isStudent()) {
            return false;
        }

        if (! $scholarship->isOpenForApplications()) {
            return false;
        }

        return ! $scholarship->applications()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can update the model's status.
     */
    public function update(User $user, ScholarshipApplication $scholarshipApplication): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScholarshipApplication $scholarshipApplication): bool
    {
        return $user->isAdmin();
    }
}
