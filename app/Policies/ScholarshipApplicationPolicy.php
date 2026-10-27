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
     *
     * A student may apply only once to each scholarship, only while they are
     * under the scholarship limit, only if the scholarship admits their
     * course, and not while they hold a place on another posting of the same
     * preset.
     */
    public function create(User $user, Scholarship $scholarship): bool
    {
        if (! $user->isStudent()) {
            return false;
        }

        if (! $scholarship->isOpenForApplications() || ! $scholarship->admitsCourse($user->course)) {
            return false;
        }

        return $user->mayApplyTo($scholarship, $user->scholarshipApplicationsWithScholarship()->get());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScholarshipApplication $scholarshipApplication): bool
    {
        return $user->isAdmin();
    }
}
