<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Support\Csv;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DecideScholarshipApplicationsFromCsv
{
    private const MAX_ROWS = 2000;

    /**
     * Column headers this importer understands, mapped from a normalized
     * CSV header (lowercased, spaces replaced with underscores) to the
     * matching field.
     *
     * @var array<string, string>
     */
    private const COLUMN_MAP = [
        'name' => 'name',
        'full_name' => 'name',
        'student_number' => 'student_number',
        'student_no' => 'student_number',
        'student_id' => 'student_number',
    ];

    /**
     * Approve every pending application matched by the given CSV, and
     * reject every other pending application for this scholarship.
     *
     * A row is matched two ways, in order:
     *
     * 1. By student number (exact, case-insensitive) — since it's unique
     *    per student, this can never be ambiguous and is tried first.
     * 2. By name (trimmed, whitespace-collapsed, case-insensitive) for
     *    whichever pending applications a student number didn't already
     *    resolve. A name shared by more than one still-unresolved pending
     *    applicant is left untouched and reported as ambiguous, since
     *    guessing which one passed would be silently wrong.
     *
     * Approvals stop once the scholarship's remaining slots run out; those
     * rows are reported instead of being approved. A single-column,
     * header-less file is still accepted and treated as a list of names,
     * for backward compatibility.
     *
     * @return array{
     *     approved: array<int, array{name: string, email: string}>,
     *     rejected: array<int, array{name: string, email: string}>,
     *     ambiguous: array<int, array{name: string, count: int}>,
     *     skipped_capacity: array<int, array{name: string, email: string}>,
     *     unmatched: array<int, array{name: ?string, student_number: ?string}>,
     * }
     */
    public function handle(Scholarship $scholarship, UploadedFile $file): array
    {
        $rows = $this->readPassedRows($file);

        $byNumber = [];
        $byName = [];

        foreach ($rows as $index => $row) {
            if ($row['student_number'] !== null) {
                $byNumber[$this->normalize($row['student_number'])] ??= $index;
            }
            if ($row['name'] !== null) {
                $byName[$this->normalize($row['name'])] ??= $index;
            }
        }

        $consumedRows = [];
        $approved = [];
        $rejected = [];
        $ambiguous = [];
        $skippedCapacity = [];
        // Pending applicants hold slots too, but this list is what decides them,
        // so only approvals already made count against the room left.
        $remainingSlots = $scholarship->slots - $scholarship->approvedApplicationsCount();

        $pendingApplications = $scholarship->applications()
            ->where('status', ApplicationStatus::Pending)
            ->with('user')
            ->get();

        // Pass 1: resolve by student number. Unique per student, so never ambiguous.
        $unresolved = collect();

        foreach ($pendingApplications as $application) {
            $number = $application->user->student_number ? $this->normalize($application->user->student_number) : null;

            if ($number !== null && isset($byNumber[$number])) {
                $consumedRows[$byNumber[$number]] = true;
                $this->approveOrSkip($application, $approved, $skippedCapacity, $remainingSlots);
            } else {
                $unresolved->push($application);
            }
        }

        // Pass 2: resolve whatever is left by name.
        foreach ($unresolved->groupBy(fn (ScholarshipApplication $application) => $this->normalize($application->user->name)) as $normalizedName => $applications) {
            if ($applications->count() > 1) {
                $ambiguous[] = ['name' => $applications->first()->user->name, 'count' => $applications->count()];

                continue;
            }

            $application = $applications->first();

            if (isset($byName[$normalizedName]) && ! isset($consumedRows[$byName[$normalizedName]])) {
                $consumedRows[$byName[$normalizedName]] = true;
                $this->approveOrSkip($application, $approved, $skippedCapacity, $remainingSlots);

                continue;
            }

            $application->update(['status' => ApplicationStatus::Rejected]);
            $rejected[] = ['name' => $application->user->name, 'email' => $application->user->email];
        }

        if ($remainingSlots < 1 && $scholarship->status === ScholarshipStatus::Open) {
            $scholarship->update(['status' => ScholarshipStatus::Closed]);
        }

        $unmatched = collect($rows)
            ->filter(fn ($row, $index) => ! isset($consumedRows[$index]))
            ->map(fn ($row) => ['name' => $row['name'], 'student_number' => $row['student_number']])
            ->values()
            ->all();

        return [
            'approved' => $approved,
            'rejected' => $rejected,
            'ambiguous' => $ambiguous,
            'skipped_capacity' => $skippedCapacity,
            'unmatched' => $unmatched,
        ];
    }

    /**
     * @param  array<int, array{name: string, email: string}>  $approved
     * @param  array<int, array{name: string, email: string}>  $skippedCapacity
     */
    private function approveOrSkip(ScholarshipApplication $application, array &$approved, array &$skippedCapacity, int &$remainingSlots): void
    {
        $summary = ['name' => $application->user->name, 'email' => $application->user->email];

        if ($remainingSlots < 1) {
            $skippedCapacity[] = $summary;

            return;
        }

        $application->update(['status' => ApplicationStatus::Approved]);
        $approved[] = $summary;
        $remainingSlots--;
    }

    /**
     * Read the "passed" list. Accepts a header row naming "name" and/or
     * "student_number" columns (in either order), or falls back to
     * treating a header-less single column as a plain list of names.
     *
     * @return array<int, array{name: ?string, student_number: ?string}>
     */
    private function readPassedRows(UploadedFile $file): array
    {
        $rows = Csv::readRows($file);

        if (empty($rows)) {
            return [];
        }

        $columns = array_map(
            fn (string $column) => self::COLUMN_MAP[$this->normalizeHeader($column)] ?? null,
            $rows[0]
        );

        if (in_array('name', $columns, true) || in_array('student_number', $columns, true)) {
            array_shift($rows);
        } else {
            $columns = ['name'];
        }

        if (count($rows) > self::MAX_ROWS) {
            throw ValidationException::withMessages([
                'file' => 'This file has more than '.self::MAX_ROWS.' rows. Please split it into smaller batches.',
            ]);
        }

        $entries = [];

        foreach ($rows as $cells) {
            if ($cells === [null]) {
                continue;
            }

            $entry = ['name' => null, 'student_number' => null];

            foreach ($columns as $index => $attribute) {
                if ($attribute === null) {
                    continue;
                }

                $value = trim((string) ($cells[$index] ?? ''));

                if ($value !== '') {
                    $entry[$attribute] = $value;
                }
            }

            if ($entry['name'] !== null || $entry['student_number'] !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    private function normalizeHeader(string $header): string
    {
        return Str::of($header)->trim()->lower()->replace(' ', '_')->value();
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->squish()->lower()->value();
    }
}
