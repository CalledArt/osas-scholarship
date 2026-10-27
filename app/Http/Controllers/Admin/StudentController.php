<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\User;
use App\Support\PersonName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * List registered students so OSAS can pick one to edit.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->value());

        $students = User::query()
            ->where('role', UserRole::Student)
            ->when($search !== '', fn (Builder $query): Builder => $query->where(fn (Builder $query): Builder => $query
                ->whereLike('email', "%{$search}%")
                ->orWhereLike('student_number', "%{$search}%")
                ->orWhereLike('first_name', "%{$search}%")
                ->orWhereLike('last_name', "%{$search}%")))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for editing a student's details.
     */
    public function edit(User $student): View
    {
        abort_unless($student->isStudent(), 403);

        return view('admin.students.edit', [
            'student' => $student,
        ]);
    }

    /**
     * Update a student's details.
     *
     * A student on leave of absence gives up the slots their pending
     * applications hold, so other students can apply for them; scholarships
     * already approved stay theirs. The save says which scholarships were
     * freed, and which of those OSAS must reopen before anyone can apply.
     */
    public function update(UpdateStudentRequest $request, User $student): RedirectResponse
    {
        $student->fill($request->validated());
        $student->name = PersonName::assemble($student->first_name, $student->middle_name, $student->last_name);

        $freedScholarships = DB::transaction(function () use ($student) {
            $student->save();

            return $student->isOnLeaveOfAbsence() ? $student->voidPendingApplications() : collect();
        });

        $status = __('Student details saved.');

        if ($freedScholarships->isNotEmpty()) {
            $status .= ' '.trans_choice(
                'Their pending application to :titles was voided, freeing its slot for another student.|Their pending applications to :titles were voided, freeing their slots for other students.',
                $freedScholarships->count(),
                ['titles' => $freedScholarships->pluck('title')->join(', ', ' and ')],
            );

            $notTakingApplications = $freedScholarships->reject->isOpenForApplications();

            if ($notTakingApplications->isNotEmpty()) {
                $status .= ' '.__('Reopen :titles, or extend the deadline, to take new applicants.', [
                    'titles' => $notTakingApplications->pluck('title')->join(', ', ' and '),
                ]);
            }
        }

        return redirect()
            ->route('admin.students.edit', $student)
            ->with('status', $status);
    }
}
