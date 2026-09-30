@php
    $results = $results ?? null;
    $imported = collect($results ?? [])->whereIn('status', ['added', 'updated']);
    $added = $imported->where('status', 'added');
    $updated = $imported->where('status', 'updated');
    $skipped = collect($results ?? [])->where('status', 'skipped');

    // Presentational shorthand for the constraints the importer enforces.
    $rules = [
        'email' => __('Required. Must end in @cvsu.edu.ph'),
        'name' => __('Used only when there are no separate name columns'),
        'year_level' => __('Whole number, 1 to 6'),
    ];
@endphp

<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Student Roster') }}</h1>

            <p class="mt-2 max-w-2xl text-lg leading-relaxed text-gray-600">
                @if ($results === null)
                    <span class="font-semibold tabular-nums text-gray-900">{{ number_format($entries->total()) }}</span>
                    {{ trans_choice('student is|students are', $entries->total()) }}
                    {{ __('on the roster. Students register with their verified CvSU Google account and enter their own details. Roster entries are kept for OSAS reference and do not control sign-in.') }}
                @elseif ($imported->isEmpty() && $skipped->isEmpty())
                    {{ __('That file had no data rows.') }}
                @else
                    {{ __('Added') }}
                    <span class="font-semibold tabular-nums text-gray-900">{{ $added->count() }}</span>,
                    {{ __('updated') }}
                    <span class="font-semibold tabular-nums text-gray-900">{{ $updated->count() }}</span>
                    {{ __('and skipped') }}
                    <span class="font-semibold tabular-nums text-gray-900">{{ $skipped->count() }}</span>
                    {{ trans_choice('row.|rows.', $skipped->count()) }}
                @endif
            </p>

            @if (session('status'))
                <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($skipped->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                        {{ trans_choice(':count row skipped|:count rows skipped', $skipped->count(), ['count' => $skipped->count()]) }}
                    </h2>

                    <div class="mt-2 overflow-x-auto">
                        <table class="w-full min-w-[36rem] text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                                    <th scope="col" class="py-2 pr-4 text-right font-medium">{{ __('Row') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Email') }}</th>
                                    <th scope="col" class="py-2 pl-4 text-left font-medium">{{ __('Why it was skipped') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($skipped as $result)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3 pr-4 text-right tabular-nums text-gray-500">{{ $result['row'] }}</td>
                                        <td class="px-4 py-3 text-gray-900">
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

            <section class="mt-10">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('On the roster') }}</h2>

                    <form method="GET" action="{{ route('admin.roster.index') }}" class="flex items-center gap-2" role="search">
                        <label for="search" class="sr-only">{{ __('Search the roster') }}</label>
                        <x-text-input id="search" name="search" type="search" :value="$search" placeholder="{{ __('Email, name or student no.') }}" class="w-64 max-w-full text-sm" />
                        <x-secondary-button type="submit">{{ __('Search') }}</x-secondary-button>
                    </form>
                </div>

                <div class="mt-2 overflow-x-auto">
                    <table class="w-full min-w-[44rem] text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                                <th scope="col" class="py-2 pr-4 text-left font-medium">{{ __('Name') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Email') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Student no.') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Course / year') }}</th>
                                <th scope="col" class="py-2 pl-4"><span class="sr-only">{{ __('Actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $entry)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4 font-medium text-gray-900">
                                        {{ \App\Support\PersonName::assemble($entry->first_name, $entry->middle_name, $entry->last_name) ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $entry->email }}</td>
                                    <td class="px-4 py-3 tabular-nums text-gray-500">{{ $entry->student_number ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500">
                                        {{ collect([$entry->course, $entry->year_level ? __('Year :level', ['level' => $entry->year_level]) : null])->filter()->implode(' · ') ?: '—' }}
                                    </td>
                                    <td class="py-3 pl-4 text-right">
                                        <form method="POST" action="{{ route('admin.roster.destroy', $entry) }}"
                                              onsubmit="return confirm(@js(__('Remove :email from the roster? This does not delete their account or prevent sign-in.', ['email' => $entry->email])))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-900 focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-red-600">
                                                {{ __('Remove') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        {{ $search !== '' ? __('No roster entries match that search.') : __('The roster is empty. Students can still register with their CvSU Google account.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $entries->links() }}
                </div>
            </section>

            <section class="mt-12 border-t border-gray-200 pt-8">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('Add students from a file') }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('CSV, up to :rows rows. An email already on the roster has its roster details updated. Importing does not create accounts, change registered student details, or send email.', ['rows' => number_format($maxRows)]) }}
                </p>

                <form method="POST" action="{{ route('admin.roster.import') }}" enctype="multipart/form-data" class="mt-4">
                    @csrf

                    <div class="flex flex-wrap items-center gap-4">
                        <x-file-input id="file" name="file" accept=".csv,.txt" class="max-w-full" required />

                        <x-primary-button>{{ __('Upload roster') }}</x-primary-button>
                    </div>

                    <x-input-error :messages="$errors->get('file')" class="mt-3" />
                </form>
            </section>

            <section class="mt-12">
                <div class="flex flex-wrap items-baseline justify-between gap-3">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Columns the importer reads') }}</h2>
                    <a href="{{ route('admin.roster.template') }}"
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
</x-app-layout>
