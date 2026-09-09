<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ImportStudentsFromCsv;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportStudentsRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StudentImportController extends Controller
{
    /**
     * One example value per canonical column, used to fill the template's sample row.
     *
     * @var array<string, string>
     */
    private const TEMPLATE_SAMPLE = [
        'name' => 'Juan Dela Cruz',
        'email' => 'juan.delacruz@cvsu.edu.ph',
        'student_number' => '21-00123',
        'course' => 'BS Information Technology',
        'year_level' => '3',
        'contact_number' => '09171234567',
        'address' => 'Indang, Cavite',
        'date_of_birth' => '2003-05-14',
    ];

    /**
     * Show the CSV upload form.
     */
    public function create(): View
    {
        return view('admin.students.import', $this->pageData());
    }

    /**
     * Process the uploaded CSV and create a student account for each valid row.
     */
    public function store(ImportStudentsRequest $request, ImportStudentsFromCsv $importer): View
    {
        return view('admin.students.import', [
            ...$this->pageData(),
            'results' => $importer->handle($request->file('file')),
        ]);
    }

    /**
     * Download a starter CSV template with the columns this importer understands.
     */
    public function template(): Response
    {
        $columns = array_keys(ImportStudentsFromCsv::acceptedColumns());

        $csv = implode(',', $columns)."\n"
            .implode(',', array_map(
                fn (string $column): string => self::TEMPLATE_SAMPLE[$column] ?? '',
                $columns
            ))."\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student-import-template.csv"',
        ]);
    }

    /**
     * @return array{studentCount: int, columns: array<string, list<string>>, maxRows: int}
     */
    private function pageData(): array
    {
        return [
            'studentCount' => User::query()->where('role', UserRole::Student)->count(),
            'columns' => ImportStudentsFromCsv::acceptedColumns(),
            'maxRows' => ImportStudentsFromCsv::MAX_ROWS,
        ];
    }
}
