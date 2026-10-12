@props(['applications', 'student'])

{{--
    The scholarships a student holds: Republic Act 10931 while they are within
    the years it covers, then approved applications whose scholarship has not
    ended. Rows read `scholarship_title`, which
    User::scholarshipApplicationsWithScholarship() joins in.
--}}
@php($hasFreeTertiaryEducation = $student->hasFreeTertiaryEducation())

<section aria-labelledby="active-heading" {{ $attributes }}>
    <h2 id="active-heading" class="text-base font-semibold text-gray-900">{{ __('Active scholarships') }}</h2>

    @if (! $hasFreeTertiaryEducation && $applications->isEmpty())
        <p class="mt-3 border-y border-gray-200 py-6 text-sm text-gray-500">
            {{ __("You don't hold a scholarship right now. Once OSAS approves one of your applications, it appears here until the scholarship ends.") }}
        </p>
    @else
        <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
            @if ($hasFreeTertiaryEducation)
                <x-free-tertiary-education :year-of-study="$student->yearOfStudy()" :covered-years="$student->freeTertiaryEducationYears()" />
            @endif

            @foreach ($applications as $application)
                <li>
                    <a href="{{ route('scholarship-applications.show', $application) }}"
                       class="group flex items-center gap-4 px-3 py-4 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cvsu-green">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere] group-hover:text-cvsu-green">{{ $application->scholarship_title }}</p>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('Applied :date', ['date' => ($application->submitted_at ?? $application->created_at)->format('M j, Y')]) }}
                            </p>
                        </div>

                        <x-badge class="shrink-0" color="green">{{ __('Active') }}</x-badge>

                        <svg class="h-5 w-5 shrink-0 text-gray-500" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
                        </svg>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
