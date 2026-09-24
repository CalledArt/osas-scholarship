<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Dashboard') }}</h1>

            <p class="mt-2 text-base text-gray-600">
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

            @if ($applicationCount > 0)
                <section class="mt-10" aria-labelledby="applications-heading" data-tour="my-applications">
                    <h2 id="applications-heading" class="text-base font-semibold text-gray-900">{{ __('My applications') }}</h2>

                    <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
                        @foreach ($applications as $application)
                            <li>
                                <a href="{{ route('scholarship-applications.show', $application) }}"
                                   class="group flex items-center gap-4 px-3 py-4 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cvsu-green">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere] group-hover:text-cvsu-green">{{ $application->scholarship_title }}</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ __('Submitted :date', ['date' => ($application->submitted_at ?? $application->created_at)->format('M j, Y')]) }}
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

            <section class="mt-10" aria-labelledby="open-heading" data-tour="open-for-you">
                <h2 id="open-heading" class="text-base font-semibold text-gray-900">{{ __('Open for you') }}</h2>

                @if ($openScholarships->isEmpty())
                    <p class="mt-3 border-y border-gray-200 py-6 text-sm text-gray-500">
                        {{ __('No scholarships are accepting applications from you right now. New ones will appear here when OSAS posts them.') }}
                    </p>
                @else
                    <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
                        @foreach ($openScholarships as $scholarship)
                            <li>
                                <a href="{{ route('scholarships.show', $scholarship) }}"
                                   class="group flex items-center gap-4 px-3 py-4 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cvsu-green">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere] group-hover:text-cvsu-green">{{ $scholarship->title }}</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            <x-deadline :scholarship="$scholarship" :urgent="true" />
                                            <span aria-hidden="true">&middot;</span>
                                            <span class="tabular-nums">{{ trans_choice(':count slot left|:count slots left', $scholarship->remainingSlots(), ['count' => $scholarship->remainingSlots()]) }}</span>
                                        </p>
                                    </div>

                                    <span class="hidden shrink-0 text-sm font-semibold text-cvsu-green sm:inline">{{ __('Apply') }} &rarr;</span>
                                    <svg class="h-5 w-5 shrink-0 text-gray-500 sm:hidden" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
                                    </svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <a href="{{ route('scholarships.index') }}"
                   class="mt-4 inline-block text-sm font-semibold text-cvsu-green underline-offset-2 hover:text-cvsu-green-dark hover:underline focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">
                    {{ __('Browse all scholarships') }} &rarr;
                </a>
            </section>
        </div>
    </div>

    <x-tutorial />
</x-app-layout>
