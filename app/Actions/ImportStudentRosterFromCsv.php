<?php

namespace App\Actions;

use App\Models\StudentRosterEntry;
use App\Support\Csv;
use App\Support\PersonName;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImportStudentRosterFromCsv
{
    public const MAX_ROWS = 5000;

    /**
     * The only email domain the roster accepts.
     */
    public const EMAIL_DOMAIN = '@cvsu.edu.ph';

    /**
     * Column headers this importer understands, mapped from a normalized
     * CSV header (lowercased, spaces replaced with underscores) to the
     * matching roster attribute.
     *
     * @var array<string, string>
     */
    private const COLUMN_MAP = [
        'email' => 'email',
        'email_address' => 'email',
        'student_number' => 'student_number',
        'student_no' => 'student_number',
        'student_id' => 'student_number',
        'first_name' => 'first_name',
        'given_name' => 'first_name',
        'middle_name' => 'middle_name',
        'last_name' => 'last_name',
        'surname' => 'last_name',
        'name' => 'name',
        'full_name' => 'name',
        'course' => 'course',
        'program' => 'course',
        'year_level' => 'year_level',
        'year' => 'year_level',
    ];

    /**
     * The canonical columns this importer understands, each mapped to every
     * header spelling accepted for it, canonical name first.
     *
     * Exposed so the roster screen and the downloadable template describe the
     * same contract this class actually enforces.
     *
     * @return array<string, list<string>>
     */
    public static function acceptedColumns(): array
    {
        $columns = [];

        foreach (self::COLUMN_MAP as $header => $attribute) {
            $columns[$attribute][] = $header;
        }

        return $columns;
    }

    /**
     * Add every valid row of the given CSV to the roster.
     *
     * A row whose email is already on the roster updates that entry, so
     * re-uploading a corrected list is safe. Each row is validated on its own
     * so one bad row does not cost the rest of the file.
     *
     * Rows are written with a single upsert per chunk rather than one query
     * each: every round trip to the database costs ~240ms here (see
     * .ai/rules/controllers.md), which would make a cohort-sized file take
     * minutes.
     *
     * @return array<int, array{row: int, status: string, email: ?string, name: ?string, error: ?string}>
     */
    public function handle(UploadedFile $file): array
    {
        $rows = Csv::readRows($file);
        $header = array_shift($rows);

        if ($header === null) {
            throw ValidationException::withMessages(['file' => 'The uploaded file is empty.']);
        }

        if (count($rows) > self::MAX_ROWS) {
            throw ValidationException::withMessages([
                'file' => 'This file has more than '.number_format(self::MAX_ROWS).' rows. Please split it into smaller batches.',
            ]);
        }

        $columns = array_map(
            fn (?string $column) => self::COLUMN_MAP[$this->normalizeHeader((string) $column)] ?? null,
            $header
        );

        if (! in_array('email', $columns, true)) {
            throw ValidationException::withMessages(['file' => 'The file needs an "email" column.']);
        }

        $results = [];
        $entries = [];
        $rowNumber = 1;

        foreach ($rows as $cells) {
            $rowNumber++;

            if ($cells === [null]) {
                continue;
            }

            $data = $this->mapRow($columns, $cells);
            $error = $this->validationError($data);
            $email = $data['email'] ?? null;
            $name = PersonName::assemble($data['first_name'] ?? null, $data['middle_name'] ?? null, $data['last_name'] ?? null) ?: null;

            if ($error === null && isset($entries[$email])) {
                $error = 'Duplicate email within this file.';
            }

            if ($error !== null) {
                $results[] = ['row' => $rowNumber, 'status' => 'skipped', 'email' => $email, 'name' => $name, 'error' => $error];

                continue;
            }

            $entries[$email] = $data;
            $results[] = ['row' => $rowNumber, 'status' => 'added', 'email' => $email, 'name' => $name, 'error' => null];
        }

        $existing = StudentRosterEntry::query()
            ->whereIn('email', array_keys($entries))
            ->pluck('email')
            ->flip();

        foreach ($results as $index => $result) {
            if ($result['status'] === 'added' && $existing->has($result['email'])) {
                $results[$index]['status'] = 'updated';
            }
        }

        $now = now();

        foreach (array_chunk($entries, 500) as $chunk) {
            StudentRosterEntry::query()->upsert(
                array_map(fn (array $entry): array => [...$entry, 'created_at' => $now, 'updated_at' => $now], $chunk),
                ['email'],
                ['student_number', 'first_name', 'middle_name', 'last_name', 'course', 'year_level', 'updated_at'],
            );
        }

        return $results;
    }

    /**
     * The first reason this row cannot go on the roster, or null if it can.
     *
     * @param  array<string, mixed>  $data
     */
    private function validationError(array $data): ?string
    {
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:255', 'ends_with:'.self::EMAIL_DOMAIN],
            'student_number' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'course' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'integer', 'between:1,6'],
        ], [
            'email.ends_with' => 'The email must be a CvSU address ('.self::EMAIL_DOMAIN.').',
        ]);

        return $validator->fails() ? $validator->errors()->first() : null;
    }

    /**
     * Pull the known columns out of a row, splitting a single "name" column
     * into parts when the file has no separate name columns.
     *
     * @param  array<int, string|null>  $columns
     * @param  array<int, string|null>  $cells
     * @return array{email: ?string, student_number: ?string, first_name: ?string, middle_name: ?string, last_name: ?string, course: ?string, year_level: ?string}
     */
    private function mapRow(array $columns, array $cells): array
    {
        $values = [];

        foreach ($columns as $index => $attribute) {
            if ($attribute === null) {
                continue;
            }

            $value = trim((string) ($cells[$index] ?? ''));
            $values[$attribute] = $value === '' ? null : $value;
        }

        $nameParts = isset($values['first_name']) || isset($values['last_name'])
            ? ['first_name' => $values['first_name'] ?? null, 'middle_name' => $values['middle_name'] ?? null, 'last_name' => $values['last_name'] ?? null]
            : PersonName::split($values['name'] ?? null);

        return [
            'email' => isset($values['email']) ? Str::lower($values['email']) : null,
            'student_number' => $values['student_number'] ?? null,
            ...$nameParts,
            'course' => $values['course'] ?? null,
            'year_level' => $values['year_level'] ?? null,
        ];
    }

    private function normalizeHeader(string $header): string
    {
        return Str::of($header)->trim()->lower()->replace(' ', '_')->value();
    }
}
