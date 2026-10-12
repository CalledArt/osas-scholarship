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
     */
    public function update(UpdateStudentRequest $request, User $student): RedirectResponse
    {
        $student->fill($request->validated());
        $student->name = PersonName::assemble($student->first_name, $student->middle_name, $student->last_name);
        $student->save();

        return redirect()
            ->route('admin.students.edit', $student)
            ->with('status', __('Student details saved.'));
    }
}
