{{--
    The read-only student roster under the dashboards' banner (asked
    2026-10-25), at the dashboards' width: the filters in one row from lg,
    then the results in a bordered panel drawn like the admin dashboard's
    postings table. From
    lg the page fits the screen and only the student list scrolls, under a
    header row that stays in view, as on a fund allocation.
--}}
<x-app-layout :title="__('Search Student')" :sheet="true">
    <div class="px-6 py-10 lg:flex lg:h-screen lg:flex-col lg:px-10 lg:py-6">
        <div class="mx-auto max-w-[100rem] lg:flex lg:min-h-0 lg:w-full lg:flex-col">
            <x-welcome-banner :heading="__('Search Student')">
                {{ __('Find registered students by name, email or student number.') }}
            </x-welcome-banner>

            <form method="GET" action="{{ route('admin.roster.index') }}" class="mt-6 rounded-lg border border-rule bg-linen-sunk p-4" role="search">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1.25fr)_auto] lg:items-end">
                    <div>
                        <x-input-label for="search" :value="__('Search student')" />
                        <x-text-input id="search" name="search" type="search" :value="$search" placeholder="{{ __('Email, name or student no.') }}" class="mt-1 block w-full" />
                    </div>

                    <div>
                        <x-input-label for="course" :value="__('Course')" />
                        <x-text-input id="course" name="course" type="search" :value="$course" placeholder="{{ __('Any course, e.g. Information Technology') }}" class="mt-1 block w-full" />
                    </div>

                    <div>
                        <x-input-label for="year_level" :value="__('Year level')" />
                        <select id="year_level" name="year_level" class="mt-1 block w-full rounded-sheet border-rule-strong shadow-sm focus:border-seal focus:ring-seal">
                            <option value="" @selected($yearLevel === null)>{{ __('All year levels') }}</option>
                            @foreach (range(1, 6) as $year)
                                <option value="{{ $year }}" @selected($yearLevel === $year)>{{ __('Year :year', ['year' => $year]) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="sort" :value="__('Sort by')" />
                        {{-- Choosing an order re-sorts at once, with whatever is in the other filters; Search still works without JavaScript. --}}
                        <select id="sort" name="sort" x-data x-on:change="$el.form.requestSubmit()" class="mt-1 block w-full rounded-sheet border-rule-strong shadow-sm focus:border-seal focus:ring-seal">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:col-span-2 lg:col-span-1">
                        <x-primary-button type="submit">{{ __('Search') }}</x-primary-button>
                        @if ($hasFilters || $sort !== 'name_asc')
                            <a href="{{ route('admin.roster.index') }}" class="rounded font-medium text-graphite underline underline-offset-4 hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Reset') }}</a>
                        @endif
                    </div>
                </div>
            </form>

            <section aria-labelledby="students-heading" data-results class="mt-6 overflow-hidden rounded-lg border border-rule bg-white lg:flex lg:min-h-0 lg:flex-col">
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 px-5 py-4">
                    <h2 id="students-heading" class="flex items-center gap-2.5 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <circle cx="9" cy="8" r="3" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.5M17.5 19a5.5 5.5 0 0 0-2.5-4.6" />
                        </svg>
                        {{ __('Registered students') }}
                    </h2>
                    <p class="text-graphite">
                        <span class="font-semibold tabular-nums text-ink">{{ number_format($students->total()) }}</span>
                        {{ trans_choice('student found|students found', $students->total()) }}
                    </p>
                </div>

                <div class="relative overflow-x-auto lg:min-h-0 lg:overflow-y-auto">
                    <table class="w-full min-w-[52rem]">
                        <thead class="sticky top-0 z-10">
                            <tr class="whitespace-nowrap bg-linen-sunk text-left text-graphite">
                                <th scope="col" class="py-2.5 pl-5 pr-4 font-medium">{{ __('Name') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Email') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Student no.') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Course') }}</th>
                                <th scope="col" class="py-2.5 pl-4 pr-5 font-medium">{{ __('Year level') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule">
                            @forelse ($students as $student)
                                <tr class="transition-colors duration-150 ease-in-out hover:bg-linen-sunk/60">
                                    <td class="py-3 pl-5 pr-4 font-semibold uppercase text-ink [overflow-wrap:anywhere]">{{ $student->surname_first_name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-graphite">{{ $student->email }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 tabular-nums text-slate">{{ $student->student_number ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate">{{ $student->course ?? '—' }}</td>
                                    <td class="whitespace-nowrap py-3 pl-4 pr-5 text-slate">{{ $student->year_level ? __('Year :level', ['level' => $student->year_level]) : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate">
                                        {{ $hasFilters ? __('No students match your search or filters.') : __('No students have registered yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if ($students->hasPages())
                <div class="mt-4 lg:shrink-0">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
