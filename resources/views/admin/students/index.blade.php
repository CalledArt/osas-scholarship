<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Edit Student Data') }}</h1>

            <p class="mt-2 max-w-2xl text-lg leading-relaxed text-gray-600">
                {{ __('Find a registered student by name, email or student number, then correct their name, student number, course, year level or program length.') }}
            </p>

            <form method="GET" action="{{ route('admin.students.index') }}" class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4" role="search">
                <div class="max-w-md">
                    <x-input-label for="search" :value="__('Search student')" />
                    <x-text-input id="search" name="search" type="search" :value="$search" placeholder="{{ __('Email, name or student no.') }}" class="mt-1 block w-full text-sm" />
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">{{ __('Search') }}</x-primary-button>
                    @if ($search !== '')
                        <a href="{{ route('admin.students.index') }}" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Reset') }}</a>
                    @endif
                </div>
            </form>

            <section class="mt-8">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('Registered students') }}</h2>
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
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Student no.') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Course') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Year level') }}</th>
                                <th scope="col" class="py-2 pl-4 text-right font-medium"><span class="sr-only">{{ __('Actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4">
                                        <p class="font-medium text-gray-900">{{ $student->name }}</p>
                                        <p class="text-gray-500">{{ $student->email }}</p>
                                    </td>
                                    <td class="px-4 py-3 tabular-nums text-gray-500">{{ $student->student_number ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $student->course ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $student->year_level ? __('Year :level', ['level' => $student->year_level]) : '—' }}</td>
                                    <td class="py-3 pl-4 text-right">
                                        <a href="{{ route('admin.students.edit', $student) }}" class="rounded font-medium text-cvsu-green underline underline-offset-4 hover:text-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">
                                            {{ __('Edit') }}<span class="sr-only"> {{ $student->name }}</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        {{ $search !== '' ? __('No students match your search.') : __('No students have registered yet.') }}
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
