<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\User;
use App\Support\TestStudents;
use Illuminate\Database\Eloquent\Builder;

class ApplyTestStudents
{
    /**
     * Have every test student who may apply to the scholarship apply to it,
     * so the pass list and Fund Allocation can be tried on a local machine
     * without signing in twenty times. Returns how many applied.
     *
     * Each is judged by the same rules as a real student (User::mayApplyTo):
     * those on leave of absence, at the scholarship limit, outside a STEM-only
     * scholarship's courses or holding another posting of its preset stay
     * out, and applications stop once the slots are taken, in the order the
     * accounts are listed. The applications carry no documents. Loaded and
     * written in one query each, since every round trip counts.
     */
    public function handle(Scholarship $scholarship): int
    {
        $emails = TestStudents::emails();
        $students = User::query()
            ->whereIn('email', $emails)
            ->where('role', UserRole::Student)
            ->with('scholarshipApplications.scholarship')
            ->get()
            ->sortBy(fn (User $student): int|false => array_search($student->email, $emails, true));

        // Read back with its held slots: a scholarship just created lacks the
        // status the database filled in, so it would read as closed.
        $scholarship = Scholarship::query()
            ->withCount(['applications as held_slots_count' => fn (Builder $query) => $query->holdingSlot()])
            ->findOrFail($scholarship->id);
        $now = now();
        $applications = [];

        foreach ($students as $student) {
            if (! $scholarship->isOpenForApplications()) {
                break;
            }

            if (! $student->mayApplyTo($scholarship, $student->scholarshipApplications)) {
                continue;
            }

            $applications[] = [
                'scholarship_id' => $scholarship->id,
                'user_id' => $student->id,
                'status' => ApplicationStatus::Pending->value,
                'submitted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $scholarship->held_slots_count++;
        }

        ScholarshipApplication::query()->insert($applications);

        return count($applications);
    }
}
