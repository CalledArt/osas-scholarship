@props(['applications', 'student'])

{{--
    What the student holds, as boxes for the page's "Mine" column: Republic
    Act 10931 while they are within the years it covers, then each approved
    application whose scholarship has not ended. Renders sibling boxes with no
    wrapper, so the column's own grid lays them out. Rows read
    `scholarship_title`, which User::scholarshipApplicationsWithScholarship()
    joins in.

    While in force, RA 10931 counts the student's year of study against the
    years it covers ("Year 4 of 5"); while paused it shows no count.
--}}
@php
    $isPaused = $student->isOnLeaveOfAbsence() && $student->isWithinFreeTertiaryEducationYears();
    $yearOfStudy = $student->yearOfStudy();
    $coveredYears = $student->freeTertiaryEducationYears();
@endphp

@if ($student->hasFreeTertiaryEducation() || $isPaused)
    <a href="{{ route('scholarships.republic-act-10931') }}" @class([
        'group block border px-5 py-4 shadow-seat transition duration-150 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2 focus-visible:ring-offset-linen',
        'border-grant/30 bg-grant-wash hover:border-grant' => ! $isPaused,
        'border-rule bg-white hover:border-seal' => $isPaused,
    ])>
        <h3 data-vt-title @class(['font-semibold', 'text-grant-deep' => ! $isPaused, 'text-ink group-hover:text-seal' => $isPaused])>{{ __('Republic Act No. 10931') }}</h3>

        @if ($isPaused)
            <p class="mt-0.5 text-slate">{{ __('Free tuition paused during leave of absence (LOA).') }}</p>
        @else
            <p class="mt-0.5 text-grant-deep">
                {{ __('Free tuition') }}
                @if ($yearOfStudy !== null && $coveredYears !== null)
                    <span aria-hidden="true">&middot;</span>
                    <span class="tabular-nums">{{ __('Year :year of :covered', ['year' => $yearOfStudy, 'covered' => $coveredYears]) }}</span>
                @endif
            </p>
            <x-badge class="mt-2" color="paper">{{ __('In force') }}</x-badge>
        @endif
    </a>
@endif

@forelse ($applications as $application)
    <a href="{{ route('scholarship-applications.show', $application) }}"
       class="group block border border-rule bg-white px-5 py-4 shadow-seat transition duration-150 ease-in-out hover:border-seal focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2 focus-visible:ring-offset-linen">
        <div class="flex items-start justify-between gap-3">
            <h3 data-vt-title class="font-semibold text-ink [overflow-wrap:anywhere] group-hover:text-seal">{{ $application->scholarship_title }}</h3>
            <x-badge class="shrink-0" color="green">{{ __('Active') }}</x-badge>
        </div>
        <p class="mt-1 text-slate">{{ __('Ref') }} <span class="font-mono">{{ $application->reference }}</span></p>
    </a>
@empty
    <div class="border border-rule bg-white px-5 py-4 shadow-seat">
        <h3 class="font-semibold text-ink">{{ __('Active scholarship') }}</h3>
        <p class="mt-0.5 text-slate">{{ __('None yet.') }}</p>
    </div>
@endforelse
