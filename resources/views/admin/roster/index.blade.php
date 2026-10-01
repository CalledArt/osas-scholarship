<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Student Roster') }}</h1>

            <p class="mt-2 max-w-2xl text-lg leading-relaxed text-gray-600">
                <span class="font-semibold tabular-nums text-gray-900">{{ number_format($students->total()) }}</span>
                {{ trans_choice('student has|students have', $students->total()) }}
                {{ __('registered. Students join the roster by verifying their CvSU Google account and completing the registration form.') }}
            </p>

            <section class="mt-10">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Registered students') }}</h2>

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
                                <th scope="col" class="py-2 pl-4 text-left font-medium">{{ __('Course / year') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4 font-medium text-gray-900">{{ $student->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $student->email }}</td>
                                    <td class="px-4 py-3 tabular-nums text-gray-500">{{ $student->student_number ?? '—' }}</td>
                                    <td class="py-3 pl-4 text-gray-500">
                                        {{ collect([$student->course, $student->year_level ? __('Year :level', ['level' => $student->year_level]) : null])->filter()->implode(' · ') ?: '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-500">
                                        {{ $search !== '' ? __('No students match that search.') : __('No students have registered yet.') }}
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
