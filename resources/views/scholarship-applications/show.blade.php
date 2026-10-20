{{--
    One application, set out as the cycle the sign-in page promises: when OSAS
    posted the scholarship, when the student applied, the review, and the
    decision. The decision is the step lit, since it is what the student came
    to read. The scholarship's held-slot count is eager loaded with it, so
    availability() spends no extra round trip.
--}}
@php
    $scholarship = $application->scholarship;
    $limit = \App\Models\User::SCHOLARSHIP_LIMIT;
    $status = $application->status;
    $hasEnded = $scholarship->status === \App\Enums\ScholarshipStatus::Ended;
@endphp

<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('scholarship-applications.index') }}" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Back to my applications') }}</a>

            <h1 data-vt-title class="mt-6 text-xl font-semibold leading-tight text-gray-900 [overflow-wrap:anywhere]">{{ $scholarship->title }}</h1>

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                <x-badge :color="match ($status) {
                    \App\Enums\ApplicationStatus::Approved => 'green',
                    \App\Enums\ApplicationStatus::Rejected => 'red',
                    default => 'gold',
                }">{{ $status->label() }}</x-badge>
                <span>{{ __('Applied :date', ['date' => $application->submitted_at->format('F j, Y')]) }}</span>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <section aria-labelledby="cycle-heading" class="mt-10">
                <h2 id="cycle-heading" class="sr-only">{{ __('Where your application stands') }}</h2>

                <ol class="-mx-3 divide-y divide-gray-100 border-y border-gray-200 [view-transition-name:cycle]">
                    <x-cycle-step :step="__('Post')" :date="$scholarship->created_at">
                        {{ trans_choice('OSAS posted this scholarship with :count slot.|OSAS posted this scholarship with :count slots.', $scholarship->slots, ['count' => $scholarship->slots]) }}
                    </x-cycle-step>

                    <x-cycle-step :step="__('Apply')" :date="$application->submitted_at">
                        {{ trans_choice('You sent :count document.|You sent :count documents.', $application->documents->count(), ['count' => $application->documents->count()]) }}
                    </x-cycle-step>

                    <x-cycle-step :step="__('Review')">
                        @if ($status === \App\Enums\ApplicationStatus::Pending)
                            {{ __('OSAS reviews every applicant and the documents they sent.') }}
                            @switch($scholarship->availability())
                                @case(\App\Enums\ScholarshipAvailability::ClosedByOsas)
                                    {{ __('OSAS has closed applications.') }}
                                    @break
                                @case(\App\Enums\ScholarshipAvailability::PastDeadline)
                                    {{ __('Applications closed on :date.', ['date' => $scholarship->deadline->format('F j, Y')]) }}
                                    @break
                                @case(\App\Enums\ScholarshipAvailability::Full)
                                    {{ __('Every slot has been filled.') }}
                                    @break
                                @default
                                    @if ($scholarship->deadline)
                                        {{ __('Applications close on :date.', ['date' => $scholarship->deadline->format('F j, Y')]) }}
                                    @else
                                        {{ __('Applications are still open.') }}
                                    @endif
                            @endswitch
                        @else
                            {{ __('OSAS reviewed every applicant and the documents they sent.') }}
                        @endif
                    </x-cycle-step>

                    <x-cycle-step :step="__('Decide')" :tone="$status === \App\Enums\ApplicationStatus::Approved ? 'green' : 'gray'" aria-current="step">
                        @switch($status)
                            @case(\App\Enums\ApplicationStatus::Approved)
                                <p class="text-lg font-semibold leading-snug text-cvsu-green-dark">{{ __('You were approved') }}</p>
                                <p class="mt-1">
                                    @if ($hasEnded)
                                        {{ __('You were on the pass list. The grant period has since ended, so this scholarship no longer counts toward the :limit you can hold.', ['limit' => $limit]) }}
                                    @else
                                        {{ __('You were on the pass list. This scholarship counts as one of the :limit you can hold until OSAS ends it.', ['limit' => $limit]) }}
                                    @endif
                                </p>
                                @break
                            @case(\App\Enums\ApplicationStatus::Rejected)
                                <p class="text-lg font-semibold leading-snug text-gray-900">{{ __('You were not approved') }}</p>
                                <p class="mt-1">{{ __('Your name was not on the pass list. This application no longer counts toward the :limit scholarships you can hold.', ['limit' => $limit]) }}</p>
                                @break
                            @default
                                <p class="text-lg font-semibold leading-snug text-gray-900">{{ __('Waiting for a decision') }}</p>
                                <p class="mt-1">{{ __('One pass list from OSAS settles every applicant at once, up to the slots available. Until then, this application counts toward the :limit scholarships you can hold.', ['limit' => $limit]) }}</p>
                        @endswitch
                    </x-cycle-step>
                </ol>
            </section>

            <section aria-labelledby="documents-heading" class="mt-12">
                <h2 id="documents-heading" class="text-base font-semibold text-gray-900">{{ __('Documents you sent') }}</h2>

                <ul class="mt-3 divide-y divide-gray-100 border-y border-gray-200">
                    @foreach ($application->documents as $document)
                        <li class="flex items-center gap-4 py-3 text-sm">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-900">{{ $document->requiredDocument->name }}</p>
                                <p class="truncate text-gray-500">{{ $document->original_name }}</p>
                            </div>
                            <a href="{{ route('scholarship-applications.documents.show', [$application, $document]) }}" class="shrink-0 rounded font-semibold text-cvsu-green hover:text-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">
                                {{ __('Download') }}<span class="sr-only"> {{ $document->requiredDocument->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
</x-app-layout>
