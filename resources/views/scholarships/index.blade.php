<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Scholarships') }}</h1>

            <x-active-scholarships :applications="$activeApplications" :student="Auth::user()" class="mt-10" />

            <x-available-scholarships :scholarships="$availableScholarships" :remaining-places="$remainingPlaces" class="mt-10" />
        </div>
    </div>
</x-app-layout>
