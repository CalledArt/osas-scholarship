<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Csv;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImportStudentsFromCsv
{
    public const MAX_ROWS = 2000;

    /**
     * Column headers this importer understands, mapped from a normalized
     * CSV header (lowercased, spaces replaced with underscores) to the
     * matching User attribute.
     *
     * @var array<string, string>
     */
    private const COLUMN_MAP = [
        'name' => 'name',
        'full_name' => 'name',
        'email' => 'email',
        'email_address' => 'email',
        'student_number' => 'student_number',
        'student_no' => 'student_number',
        'student_id' => 'student_number',
        'course' => 'course',
        'program' => 'course',
        'year_level' => 'year_level',
        'year' => 'year_level',
        'contact_number' => 'contact_number',
        'contact' => 'contact_number',
        'phone' => 'contact_number',
        'address' => 'address',
        'date_of_birth' => 'date_of_birth',
        'birthdate' => 'date_of_birth',
    ];

    /**
     * The canonical columns this importer understands, each mapped to every
     * header spelling accepted for it, canonical name first.
     *
     * Exposed so the import screen and the downloadable template describe the
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
     * Create a student account for each valid row in the given CSV file.
     *
     * Each row is validated independently so one bad row does not abort
     * the rest of the batch. Every created account gets a random password,
     * returned here in plaintext exactly once — it is never persisted or
     * logged, since the hashed value is all the database ever stores.
     *
     * @return array<int, array{row: int, status: string, name: ?string, email: ?string, student_number: ?string, password: ?string, error: ?string}>
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
                'file' => 'This file has more than '.self::MAX_ROWS.' rows. Please split it into smaller batches.',
            ]);
        }

        $columns = array_map(
            fn (string $column) => self::COLUMN_MAP[$this->normalizeHeader($column)] ?? null,
            $header
        );

        $results = [];
        $seenEmails = [];
        $rowNumber = 1;

        foreach ($rows as $cells) {
            $rowNumber++;

            if ($cells === [null]) {
                continue;
            }

            $data = $this->mapRow($columns, $cells);
            $results[] = $this->processRow($rowNumber, $data, $seenEmails);
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $seenEmails
     * @return array{row: int, status: string, name: ?string, email: ?string, student_number: ?string, password: ?string, error: ?string}
     */
    private function processRow(int $rowNumber, array $data, array &$seenEmails): array
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'student_number' => ['nullable', 'string', 'max:255', 'unique:users,student_number'],
            'course' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'integer', 'between:1,6'],
            'contact_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        $name = $data['name'] ?? null;
        $email = isset($data['email']) ? Str::lower(trim($data['email'])) : null;
        $studentNumber = $data['student_number'] ?? null;

        if ($validator->fails()) {
            return $this->skipped($rowNumber, $name, $email, $studentNumber, $validator->errors()->first());
        }

        if (in_array($email, $seenEmails, true)) {
            return $this->skipped($rowNumber, $name, $email, $studentNumber, 'Duplicate email within this file.');
        }

        $seenEmails[] = $email;
        $password = Str::password(12);

        User::create([
            'name' => trim($name),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'must_change_password' => true,
            'student_number' => $studentNumber,
            'course' => $data['course'] ?? null,
            'year_level' => $data['year_level'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
        ]);

        return [
            'row' => $rowNumber,
            'status' => 'created',
            'name' => trim($name),
            'email' => $email,
            'student_number' => $studentNumber,
            'password' => $password,
            'error' => null,
        ];
    }

    /**
     * @return array{row: int, status: string, name: ?string, email: ?string, student_number: ?string, password: ?string, error: ?string}
     */
    private function skipped(int $rowNumber, ?string $name, ?string $email, ?string $studentNumber, string $error): array
    {
        return [
            'row' => $rowNumber,
            'status' => 'skipped',
            'name' => $name,
            'email' => $email,
            'student_number' => $studentNumber,
            'password' => null,
            'error' => $error,
        ];
    }

    /**
     * @param  array<int, string|null>  $columns
     * @param  array<int, string|null>  $cells
     * @return array<string, mixed>
     */
    private function mapRow(array $columns, array $cells): array
    {
        $data = [];

        foreach ($columns as $index => $attribute) {
            if ($attribute === null) {
                continue;
            }

            $value = trim((string) ($cells[$index] ?? ''));
            $data[$attribute] = $value === '' ? null : $value;
        }

        return $data;
    }

    private function normalizeHeader(string $header): string
    {
        return Str::of($header)->trim()->lower()->replace(' ', '_')->value();
    }
}
