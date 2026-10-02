<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Dashboard') }}</h1>

                    <p class="mt-2 text-base text-gray-600">
                        @if ($readyForPassList->isNotEmpty())
                            <span class="font-semibold tabular-nums text-gray-900">{{ $readyForPassList->count() }}</span>
                            {{ trans_choice('scholarship is ready for a pass list.|scholarships are ready for a pass list.', $readyForPassList->count()) }}
                            @if ($takingApplications->isNotEmpty())
                                <span class="font-semibold tabular-nums text-gray-900">{{ $takingApplications->count() }}</span>
                                {{ trans_choice('more is still taking applications.|more are still taking applications.', $takingApplications->count()) }}
                            @endif
                        @elseif ($takingApplications->isNotEmpty())
                            {{ __('No pass lists are due yet.') }}
                            <span class="font-semibold tabular-nums text-gray-900">{{ $takingApplications->count() }}</span>
                            {{ trans_choice('scholarship with applicants is still taking applications.|scholarships with applicants are still taking applications.', $takingApplications->count()) }}
                        @elseif ($scholarshipCount === 0)
                            {{ __('No scholarships have been posted yet.') }}
                        @else
                            {{ __("You're all caught up. No applications need a decision.") }}
                        @endif
                    </p>
                </div>

                <a href="{{ route('admin.scholarships.create') }}"
                   class="inline-flex shrink-0 items-center justify-center rounded-lg bg-cvsu-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-gold focus-visible:ring-offset-2">
                    {{ __('Post scholarship') }}
                </a>
            </div>

            @php
                $queues = [
                    [
                        'key' => 'ready',
                        'title' => __('Ready for a pass list'),
                        'hint' => __('Closed to new applicants. Longest wait first.'),
                        'scholarships' => $readyForPassList,
                    ],
                    [
                        'key' => 'open',
                        'title' => __('Still taking applications'),
                        'hint' => __('A pass list rejects every applicant not on it, so upload one after the scholarship closes.'),
                        'scholarships' => $takingApplications,
                    ],
                ];
            @endphp

            @if ($readyForPassList->isNotEmpty() || $takingApplications->isNotEmpty())
                <div class="mt-10 space-y-10">
                    @foreach ($queues as $queue)
                        @continue($queue['scholarships']->isEmpty())

                        <section aria-labelledby="{{ $queue['key'] }}-heading">
                            <h2 id="{{ $queue['key'] }}-heading" class="text-base font-semibold text-gray-900">{{ $queue['title'] }}</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ $queue['hint'] }}</p>

                            <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
                                @foreach ($queue['scholarships'] as $scholarship)
                                    <li>
                                        <a href="{{ route('admin.scholarships.show', $scholarship) }}"
                                           class="group flex items-center gap-4 px-3 py-4 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cvsu-green">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere] group-hover:text-cvsu-green">{{ $scholarship->title }}</p>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    <x-availability :scholarship="$scholarship" />
                                                    <span aria-hidden="true">&middot;</span>
                                                    <span class="tabular-nums">{{ __(':filled of :slots slots filled', ['filled' => $scholarship->approved_applications_count, 'slots' => $scholarship->slots]) }}</span>
                                                </p>
                                            </div>

                                            <p class="shrink-0 text-right text-sm text-gray-500">
                                                <span class="block text-lg font-semibold leading-6 tabular-nums text-gray-900">{{ $scholarship->pending_count }}</span>
                                                {{ __('pending') }}
                                            </p>

                                            <span class="hidden shrink-0 text-sm font-semibold text-cvsu-green sm:inline">{{ __('Review') }} &rarr;</span>
                                            <svg class="h-5 w-5 shrink-0 text-gray-500 sm:hidden" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
                                            </svg>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
            @endif

            @if ($scholarshipCount > 0)
                <a href="{{ route('admin.scholarships.index') }}"
                   class="mt-6 inline-block text-sm font-semibold text-cvsu-green underline-offset-2 hover:text-cvsu-green-dark hover:underline focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">
                    {{ trans_choice('View the :count scholarship|View all :count scholarships', $scholarshipCount, ['count' => $scholarshipCount]) }} &rarr;
                </a>
            @endif
        </div>
    </div>
</x-app-layout>
