@php
    $results = $results ?? null;
    $created = collect($results ?? [])->where('status', 'created');
    $skipped = collect($results ?? [])->where('status', 'skipped');
    $unemailed = $created->where('emailed', false);

    // Presentational shorthand for the constraints processRow() enforces.
    $rules = [
        'name' => __('Required'),
        'email' => __('Required, and not already in use'),
        'student_number' => __('Not already in use'),
        'year_level' => __('Whole number, 1 to 6'),
        'date_of_birth' => __('YYYY-MM-DD'),
    ];
@endphp

<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Import Students') }}</h1>

            <p class="mt-2 max-w-2xl text-lg leading-relaxed text-gray-600">
                @if ($results === null)
                    <span class="font-semibold tabular-nums text-gray-900">{{ number_format($studentCount) }}</span>
                    {{ trans_choice('student account exists.|student accounts exist.', $studentCount) }}
                    {{ __('Each row you upload creates one more and emails the student a one-time password, which the portal makes them change when they first sign in.') }}
                @elseif ($created->isEmpty() && $skipped->isEmpty())
                    {{ __('That file had no data rows.') }}
                @elseif ($skipped->isEmpty())
                    {{ __('Created') }}
                    <span class="font-semibold tabular-nums text-gray-900">{{ $created->count() }}</span>
                    {{ trans_choice('account.|accounts.', $created->count()) }}
                @elseif ($created->isEmpty())
                    {{ __('Created nothing.') }}
                    {{ __('All') }}
                    <span class="font-semibold tabular-nums text-gray-900">{{ $skipped->count() }}</span>
                    {{ trans_choice('row was skipped.|rows were skipped.', $skipped->count()) }}
                @else
                    {{ __('Created') }}
                    <span class="font-semibold tabular-nums text-gray-900">{{ $created->count() }}</span>
                    {{ trans_choice('account|accounts', $created->count()) }}
                    {{ __('and skipped') }}
                    <span class="font-semibold tabular-nums text-gray-900">{{ $skipped->count() }}</span>
                    {{ trans_choice('row.|rows.', $skipped->count()) }}
                @endif
            </p>

            @if ($created->isNotEmpty())
                <div class="mt-6 rounded-lg border border-cvsu-gold/50 bg-cvsu-gold/15 px-4 py-3 text-sm text-cvsu-green-dark">
                    {{ __('Each student has been emailed their password. It is shown here once and never stored, so download it now, or copy it out before you leave this page.') }}
                </div>

                @if ($unemailed->isNotEmpty())
                    <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ trans_choice(
                            'The email to :count student could not be sent, so their account exists but they have not been told. Pass the password below to them yourself.|The emails to :count students could not be sent, so their accounts exist but they have not been told. Pass the passwords below to them yourself.',
                            $unemailed->count(),
                            ['count' => $unemailed->count()],
                        ) }}
                    </div>
                @endif

                <section class="mt-10">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ trans_choice(':count account created|:count accounts created', $created->count(), ['count' => $created->count()]) }}
                        </h2>

                        <x-secondary-button type="button" id="download-credentials">
                            {{ __('Download credentials (.csv)') }}
                        </x-secondary-button>
                    </div>

                    <div class="mt-2 overflow-x-auto">
                        <table class="w-full min-w-[52rem] text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                                    <th scope="col" class="py-2 pr-4 text-right font-medium">{{ __('Row') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Name') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Student no.') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Email') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('One-time password') }}</th>
                                    <th scope="col" class="py-2 pl-4 text-left font-medium">{{ __('Emailed') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($created as $result)
                                    <tr class="border-b border-gray-100"
                                        data-credential
                                        data-name="{{ $result['name'] }}"
                                        data-student-number="{{ $result['student_number'] }}"
                                        data-email="{{ $result['email'] }}"
                                        data-password="{{ $result['password'] }}">
                                        <td class="py-3 pr-4 text-right tabular-nums text-gray-500">{{ $result['row'] }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $result['name'] }}</td>
                                        <td class="px-4 py-3 tabular-nums text-gray-500">
                                            {{ $result['student_number'] ?: '' }}
                                            @unless ($result['student_number'])
                                                <span class="text-gray-400" aria-hidden="true">&mdash;</span>
                                                <span class="sr-only">{{ __('None') }}</span>
                                            @endunless
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">{{ $result['email'] }}</td>
                                        <td class="px-4 py-3">
                                            <span class="select-all font-mono text-[13px] text-gray-900">{{ $result['password'] }}</span>
                                        </td>
                                        <td class="py-3 pl-4">
                                            @if ($result['emailed'])
                                                <span class="text-gray-500">{{ __('Sent') }}</span>
                                            @else
                                                <span class="font-medium text-red-700">{{ __('Not sent') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if ($skipped->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                        {{ trans_choice(':count row skipped|:count rows skipped', $skipped->count(), ['count' => $skipped->count()]) }}
                    </h2>

                    <div class="mt-2 overflow-x-auto">
                        <table class="w-full min-w-[40rem] text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                                    <th scope="col" class="py-2 pr-4 text-right font-medium">{{ __('Row') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Name') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Email') }}</th>
                                    <th scope="col" class="py-2 pl-4 text-left font-medium">{{ __('Why it was skipped') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($skipped as $result)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3 pr-4 text-right tabular-nums text-gray-500">{{ $result['row'] }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $result['name'] ?: '' }}
                                            @unless ($result['name'])
                                                <span class="font-normal text-gray-400" aria-hidden="true">&mdash;</span>
                                                <span class="sr-only">{{ __('None') }}</span>
                                            @endunless
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">
                                            {{ $result['email'] ?: '' }}
                                            @unless ($result['email'])
                                                <span class="text-gray-400" aria-hidden="true">&mdash;</span>
                                                <span class="sr-only">{{ __('None') }}</span>
                                            @endunless
                                        </td>
                                        <td class="py-3 pl-4 text-red-700">{{ $result['error'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <section class="mt-12 border-t border-gray-200 pt-8">
                <h2 class="text-sm font-semibold text-gray-900">
                    {{ $results === null ? __('Upload a file') : __('Import another file') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('CSV, up to :rows rows. A row whose email or student number is already taken is skipped, and the rest of the file still goes through.', ['rows' => number_format($maxRows)]) }}
                </p>

                <form method="POST" action="{{ route('admin.students.import.store') }}" enctype="multipart/form-data" class="mt-4">
                    @csrf

                    <div class="flex flex-wrap items-center gap-4">
                        <x-file-input id="file" name="file" accept=".csv,.txt" class="max-w-full" required />

                        <x-primary-button>{{ __('Import Students') }}</x-primary-button>
                    </div>

                    <x-input-error :messages="$errors->get('file')" class="mt-3" />
                </form>
            </section>

            <section class="mt-12">
                <div class="flex flex-wrap items-baseline justify-between gap-3">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Columns the importer reads') }}</h2>
                    <a href="{{ route('admin.students.import.template') }}"
                       class="text-sm font-medium text-cvsu-green underline-offset-2 hover:text-cvsu-green-dark hover:underline focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">
                        {{ __('Download a template') }}
                    </a>
                </div>

                <div class="mt-2 overflow-x-auto">
                    <table class="w-full min-w-[40rem] text-sm">
                        <caption class="sr-only">{{ __('Accepted column headers and the rule applied to each') }}</caption>
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                                <th scope="col" class="py-2 pr-4 text-left font-medium">{{ __('Column') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Also accepted') }}</th>
                                <th scope="col" class="py-2 pl-4 text-left font-medium">{{ __('Rule') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($columns as $column => $aliases)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4">
                                        <span class="font-mono text-[13px] text-gray-900">{{ $column }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if (count($aliases) > 1)
                                            <span class="font-mono text-[13px] text-gray-500">{{ implode(', ', array_slice($aliases, 1)) }}</span>
                                        @else
                                            <span class="text-gray-400" aria-hidden="true">&mdash;</span>
                                            <span class="sr-only">{{ __('No alternatives') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 pl-4 text-gray-500">
                                        @if (isset($rules[$column]))
                                            {{ $rules[$column] }}
                                        @else
                                            <span class="text-gray-400" aria-hidden="true">&mdash;</span>
                                            <span class="sr-only">{{ __('Optional') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="mt-3 text-sm text-gray-500">
                    {{ __('Headers are matched case-insensitively, and any column not listed here is ignored.') }}
                </p>
            </section>
        </div>
    </div>

    @if ($created->isNotEmpty())
        <script>
            document.getElementById('download-credentials').addEventListener('click', function () {
                const rows = [['name', 'student_number', 'email', 'password']];

                document.querySelectorAll('tr[data-credential]').forEach(function (tr) {
                    rows.push([tr.dataset.name, tr.dataset.studentNumber, tr.dataset.email, tr.dataset.password]);
                });

                const quote = function (value) {
                    return '"' + String(value ?? '').replace(/"/g, '""') + '"';
                };

                // Name, student number and email come from the uploaded file, so a leading
                // =, +, - or @ is neutralized against spreadsheet formula injection.
                const guard = function (value) {
                    const text = String(value ?? '');

                    return quote(/^[=+\-@]/.test(text) ? "'" + text : text);
                };

                const csv = rows.map(function (row, index) {
                    // The generated password is never rewritten: Str::password() can start
                    // with "-" or "@", and a prefix would hand the student a credential
                    // that does not work. It is random, so it is not an injection vector.
                    const cells = index === 0
                        ? row.map(quote)
                        : [guard(row[0]), guard(row[1]), guard(row[2]), quote(row[3])];

                    return cells.join(',');
                }).join('\r\n');

                const blob = new Blob([csv], { type: 'text/csv' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'student-credentials.csv';
                link.click();
                URL.revokeObjectURL(url);
            });
        </script>
    @endif
</x-app-layout>
