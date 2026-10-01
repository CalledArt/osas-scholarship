<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentRosterController extends Controller
{
    /**
     * List every registered student.
     *
     * The roster is filled by the registration form students complete after
     * verifying their CvSU Google account; OSAS does not import it.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->value());

        $students = User::query()
            ->where('role', UserRole::Student)
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereLike('email', "%{$search}%")
                ->orWhereLike('student_number', "%{$search}%")
                ->orWhereLike('first_name', "%{$search}%")
                ->orWhereLike('last_name', "%{$search}%")))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(50)
            ->withQueryString();

        return view('admin.roster.index', [
            'students' => $students,
            'search' => $search,
        ]);
    }
}
