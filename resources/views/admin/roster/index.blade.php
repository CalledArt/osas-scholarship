<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Search Student') }}</h1>

            <p class="mt-2 max-w-2xl text-lg leading-relaxed text-gray-600">
                {{ __('Find registered students by name, email or student number. Narrow the results by year level or course, and choose how to sort them.') }}
            </p>

            <form method="GET" action="{{ route('admin.roster.index') }}" class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4" role="search">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="search" :value="__('Search student')" />
                        <x-text-input id="search" name="search" type="search" :value="$search" placeholder="{{ __('Email, name or student no.') }}" class="mt-1 block w-full text-sm" />
                    </div>

                    <div>
                        <x-input-label for="sort" :value="__('Sort by')" />
                        <select id="sort" name="sort" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-cvsu-green focus:ring-cvsu-green">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="year_level" :value="__('Year level')" />
                        <select id="year_level" name="year_level" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-cvsu-green focus:ring-cvsu-green">
                            <option value="" @selected($yearLevel === null)>{{ __('All year levels') }}</option>
                            @foreach (range(1, 6) as $year)
                                <option value="{{ $year }}" @selected($yearLevel === $year)>{{ __('Year :year', ['year' => $year]) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="course" :value="__('Course')" />
                        <x-text-input id="course" name="course" type="search" :value="$course" placeholder="{{ __('Any course, e.g. Information Technology') }}" class="mt-1 block w-full text-sm" />
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">{{ __('Search') }}</x-primary-button>
                    @if ($hasFilters || $sort !== 'name_asc')
                        <a href="{{ route('admin.roster.index') }}" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Reset') }}</a>
                    @endif
                </div>
            </form>

            <section class="mt-8">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Registered students') }}</h2>
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold tabular-nums text-gray-900">{{ number_format($students->total()) }}</span>
                        {{ trans_choice('student found|students found', $students->total()) }}
                    </p>
                </div>

                <div class="mt-2 overflow-x-auto">
                    <table class="w-full min-w-[52rem] text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                                <th scope="col" class="py-2 pr-4 text-left font-medium">{{ __('Name') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Email') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Student no.') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Course') }}</th>
                                <th scope="col" class="py-2 pl-4 text-left font-medium">{{ __('Year level') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4 font-medium text-gray-900">{{ $student->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $student->email }}</td>
                                    <td class="px-4 py-3 tabular-nums text-gray-500">{{ $student->student_number ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $student->course ?? '—' }}</td>
                                    <td class="py-3 pl-4 text-gray-500">{{ $student->year_level ? __('Year :level', ['level' => $student->year_level]) : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        {{ $hasFilters ? __('No students match your search or filters.') : __('No students have registered yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $students->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
