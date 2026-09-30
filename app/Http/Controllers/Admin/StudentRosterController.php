<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ImportStudentRosterFromCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportStudentRosterRequest;
use App\Models\StudentRosterEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StudentRosterController extends Controller
{
    /**
     * One example value per canonical column, used to fill the template's sample row.
     *
     * @var array<string, string>
     */
    private const TEMPLATE_SAMPLE = [
        'email' => 'juan.delacruz@cvsu.edu.ph',
        'student_number' => '21-00123',
        'first_name' => 'Juan',
        'middle_name' => 'Santos',
        'last_name' => 'Dela Cruz',
        'course' => 'BS Information Technology',
        'year_level' => '3',
    ];

    /**
     * Show the roster, with the upload form for adding to it.
     */
    public function index(Request $request): View
    {
        return view('admin.roster.index', $this->pageData($request));
    }

    /**
     * Add or update roster entries from an uploaded CSV.
     */
    public function import(ImportStudentRosterRequest $request, ImportStudentRosterFromCsv $importer): View
    {
        return view('admin.roster.index', [
            ...$this->pageData($request),
            'results' => $importer->handle($request->file('file')),
        ]);
    }

    /**
     * Download a starter CSV with the columns the importer reads.
     */
    public function template(): Response
    {
        $columns = array_keys(self::TEMPLATE_SAMPLE);

        $csv = implode(',', $columns)."\n".implode(',', self::TEMPLATE_SAMPLE)."\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student-roster-template.csv"',
        ]);
    }

    /**
     * Remove an OSAS reference entry without changing the student's account.
     */
    public function destroy(StudentRosterEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back()->with('status', __(':email was removed from the roster.', ['email' => $entry->email]));
    }

    /**
     * @return array{entries: LengthAwarePaginator, search: string, columns: array<string, list<string>>, maxRows: int}
     */
    private function pageData(Request $request): array
    {
        $search = trim($request->string('search')->value());

        $entries = StudentRosterEntry::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereLike('email', "%{$search}%")
                ->orWhereLike('student_number', "%{$search}%")
                ->orWhereLike('first_name', "%{$search}%")
                ->orWhereLike('last_name', "%{$search}%")))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(50)
            ->withPath(route('admin.roster.index'))
            ->withQueryString();

        return [
            'entries' => $entries,
            'search' => $search,
            'columns' => ImportStudentRosterFromCsv::acceptedColumns(),
            'maxRows' => ImportStudentRosterFromCsv::MAX_ROWS,
        ];
    }
}
