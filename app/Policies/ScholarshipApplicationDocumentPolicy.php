<?php

namespace App\Policies;

use App\Models\ScholarshipApplicationDocument;
use App\Models\User;

class ScholarshipApplicationDocumentPolicy
{
    /**
     * Determine whether the user can download the document.
     */
    public function view(User $user, ScholarshipApplicationDocument $scholarshipApplicationDocument): bool
    {
        return $user->isAdmin() || $user->id === $scholarshipApplicationDocument->application->user_id;
    }
}
