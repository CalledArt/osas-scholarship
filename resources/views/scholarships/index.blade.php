{{--
    The student's scholarships as a browsable grid of record cards: what they
    hold, then what they can still apply to. The Dashboard shows the same two
    lists as rows; the grid is this page's own, for scanning at full width.
--}}
<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-7xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Scholarships') }}</h1>

            <x-active-scholarships :applications="$activeApplications" :student="Auth::user()" :grid="true" class="mt-10" />

            <x-available-scholarships :scholarships="$availableScholarships" :remaining-places="$remainingPlaces" :grid="true" class="mt-14" />
        </div>
    </div>
</x-app-layout>
