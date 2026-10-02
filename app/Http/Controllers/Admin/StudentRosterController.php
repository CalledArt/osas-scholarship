<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
        $course = trim($request->string('course')->value());
        $yearLevel = $request->string('year_level')->value();
        $yearLevel = in_array($yearLevel, ['1', '2', '3', '4', '5', '6'], true) ? (int) $yearLevel : null;
        $sortOptions = [
            'name_asc' => __('Surname: A–Z'),
            'name_desc' => __('Surname: Z–A'),
            'student_number_asc' => __('Student number: ascending'),
            'student_number_desc' => __('Student number: descending'),
            'year_level_asc' => __('Year level: lowest first'),
            'year_level_desc' => __('Year level: highest first'),
        ];
        $sort = $request->string('sort', 'name_asc')->value();
        $sort = array_key_exists($sort, $sortOptions) ? $sort : 'name_asc';

        [$sortColumn, $sortDirection] = match ($sort) {
            'name_desc' => ['last_name', 'desc'],
            'student_number_asc' => ['student_number', 'asc'],
            'student_number_desc' => ['student_number', 'desc'],
            'year_level_asc' => ['year_level', 'asc'],
            'year_level_desc' => ['year_level', 'desc'],
            default => ['last_name', 'asc'],
        };

        $students = User::query()
            ->where('role', UserRole::Student)
            ->when($search !== '', fn (Builder $query): Builder => $query->where(fn (Builder $query): Builder => $query
                ->whereLike('email', "%{$search}%")
                ->orWhereLike('student_number', "%{$search}%")
                ->orWhereLike('first_name', "%{$search}%")
                ->orWhereLike('last_name', "%{$search}%")))
            ->when($yearLevel !== null, fn (Builder $query): Builder => $query->where('year_level', $yearLevel))
            ->when($course !== '', fn (Builder $query): Builder => $query->whereLike('course', "%{$course}%"))
            ->orderBy($sortColumn, $sortDirection)
            ->when($sortColumn !== 'last_name', fn (Builder $query): Builder => $query->orderBy('last_name'))
            ->orderBy('first_name', $sortColumn === 'last_name' ? $sortDirection : 'asc')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.roster.index', [
            'students' => $students,
            'search' => $search,
            'course' => $course,
            'yearLevel' => $yearLevel,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'hasFilters' => $search !== '' || $course !== '' || $yearLevel !== null,
        ]);
    }
}
