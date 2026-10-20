<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Dashboard') }}</h1>

            <p class="mt-3 max-w-3xl text-pretty text-lg leading-relaxed text-gray-600">
                {{ __('Welcome, :name!', ['name' => Auth::user()->full_name]) }}
                @if ($pendingCount > 0)
                    <span class="font-semibold tabular-nums text-gray-900">{{ $pendingCount }}</span>
                    {{ trans_choice('application is waiting for a decision.|applications are waiting for a decision.', $pendingCount) }}
                @elseif ($applicationCount === 0)
                    {{ __("You haven't applied to any scholarships yet.") }}
                @else
                    {{ __('Every application you sent has been decided.') }}
                @endif
            </p>

            <x-active-scholarships :applications="$activeApplications" :student="Auth::user()" class="mt-12" />

            <x-available-scholarships :scholarships="$availableScholarships" :remaining-places="$remainingPlaces" class="mt-10">
                @if ($availableScholarshipCount > $availableScholarships->count())
                    <a href="{{ route('scholarships.index') }}"
                       class="mt-4 inline-block text-sm font-semibold text-cvsu-green underline-offset-2 hover:text-cvsu-green-dark hover:underline focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">
                        {{ __('View all :count available scholarships', ['count' => $availableScholarshipCount]) }} &rarr;
                    </a>
                @endif
            </x-available-scholarships>

            @if ($applicationCount > 0)
                <section class="mt-10" aria-labelledby="applications-heading">
                    <h2 id="applications-heading" class="text-base font-semibold text-gray-900">{{ __('My applications') }}</h2>

                    <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
                        @foreach ($applications as $application)
                            <li>
                                <a href="{{ route('scholarship-applications.show', $application) }}"
                                   class="group flex items-center gap-4 px-3 py-4 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cvsu-green">
                                    <div class="min-w-0 flex-1">
                                        <p data-vt-title class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere] group-hover:text-cvsu-green">{{ $application->scholarship_title }}</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ __('Applied :date', ['date' => ($application->submitted_at ?? $application->created_at)->format('M j, Y')]) }}
                                        </p>
                                    </div>

                                    <x-badge class="shrink-0" :color="match ($application->status->value) {
                                        'approved' => 'green',
                                        'rejected' => 'red',
                                        default => 'gold',
                                    }">
                                        {{ $application->status->label() }}
                                    </x-badge>

                                    <svg class="h-5 w-5 shrink-0 text-gray-500" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
                                    </svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    @if ($applicationCount > $applications->count())
                        <a href="{{ route('scholarship-applications.index') }}"
                           class="mt-4 inline-block text-sm font-semibold text-cvsu-green underline-offset-2 hover:text-cvsu-green-dark hover:underline focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">
                            {{ __('View all :count applications', ['count' => $applicationCount]) }} &rarr;
                        </a>
                    @endif
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
