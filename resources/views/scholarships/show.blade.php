{{--
    A scholarship as the student meets it: its standing at a glance, then the
    cycle the sign-in page promises, with the Apply step lit because that is
    where the student acts, or learns why they can't. The details follow.
--}}
@php
    $availability = $scholarship->availability();
    $isOpen = $availability === \App\Enums\ScholarshipAvailability::Open;
    $documentCount = $scholarship->requiredDocuments->count();
@endphp

<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('scholarships.index') }}" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Back to scholarships') }}</a>

            <h1 data-vt-title class="mt-6 text-xl font-semibold leading-tight text-gray-900 [overflow-wrap:anywhere]">{{ $scholarship->title }}</h1>

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                <x-badge :color="$isOpen ? 'green' : 'gray'">
                    {{ match ($availability) {
                        \App\Enums\ScholarshipAvailability::Open => __('Open'),
                        \App\Enums\ScholarshipAvailability::Full => __('Full'),
                        default => __('Closed'),
                    } }}
                </x-badge>
                <x-deadline :scholarship="$scholarship" :urgent="$isOpen" />
                <span class="tabular-nums"><span aria-hidden="true">&middot;</span> {{ __(':remaining of :slots slots left', ['remaining' => $scholarship->remainingSlots(), 'slots' => $scholarship->slots]) }}</span>
                <span><span aria-hidden="true">&middot;</span> {{ __(':type scholarship', ['type' => $scholarship->type->label()]) }}</span>
            </div>

            <section aria-labelledby="cycle-heading" class="mt-10">
                <h2 id="cycle-heading" class="sr-only">{{ __('How this scholarship is decided') }}</h2>

                <ol class="-mx-3 divide-y divide-gray-100 border-y border-gray-200 [view-transition-name:cycle]">
                    <x-cycle-step :step="__('Post')" :date="$scholarship->created_at">
                        {{ trans_choice('OSAS posted this scholarship with :count slot.|OSAS posted this scholarship with :count slots.', $scholarship->slots, ['count' => $scholarship->slots]) }}
                    </x-cycle-step>

                    <x-cycle-step :step="__('Apply')" :date="$application?->submitted_at" tone="gray" aria-current="step">
                        @if ($application)
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                <span class="text-gray-900">{{ __('You applied.') }}</span>
                                <x-badge :color="match ($application->status) {
                                    \App\Enums\ApplicationStatus::Approved => 'green',
                                    \App\Enums\ApplicationStatus::Rejected => 'red',
                                    default => 'gold',
                                }">{{ $application->status->label() }}</x-badge>
                            </div>
                            <a href="{{ route('scholarship-applications.show', $application) }}" class="mt-2 inline-block rounded font-semibold text-cvsu-green hover:text-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2 focus-visible:ring-offset-gray-50">
                                {{ __('View your application') }} &rarr;
                            </a>
                        @elseif ($isOpen && ! $admitsCourse)
                            {{ __('Only students in a STEM course can apply, and your registered course, :course, is not one of them.', ['course' => auth()->user()->course ?? __('not set')]) }}
                        @elseif ($isOpen && $remainingPlaces > 0)
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-gray-900">
                                        @if ($documentCount > 0)
                                            {{ trans_choice('Send the :count document listed below.|Send the :count documents listed below.', $documentCount, ['count' => $documentCount]) }}
                                        @else
                                            {{ __('No documents are needed to apply.') }}
                                        @endif
                                        @if ($scholarship->deadline)
                                            {{ __('Applications close on :date.', ['date' => $scholarship->deadline->format('F j, Y')]) }}
                                        @endif
                                    </p>
                                    <p class="mt-1 text-gray-500">{{ trans_choice('You can apply to :count more scholarship.|You can apply to :count more scholarships.', $remainingPlaces, ['count' => $remainingPlaces]) }}</p>
                                </div>
                                <a href="{{ route('scholarships.apply', $scholarship) }}" class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-lg border border-transparent bg-cvsu-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-gold focus-visible:ring-offset-2 focus-visible:ring-offset-gray-50 sm:self-auto">
                                    {{ __('Apply now') }}
                                </a>
                            </div>
                        @elseif ($isOpen)
                            {{ __('You already hold :limit scholarships, counting applications waiting for a decision. You can apply again once an application is rejected or one of your scholarships ends.', ['limit' => \App\Models\User::SCHOLARSHIP_LIMIT]) }}
                        @else
                            {{ __('This scholarship is no longer accepting applications.') }}
                        @endif
                    </x-cycle-step>

                    <x-cycle-step :step="__('Review')">
                        {{ __('OSAS reviews every applicant and the documents they sent.') }}
                    </x-cycle-step>

                    <x-cycle-step :step="__('Decide')">
                        {{ trans_choice('One pass list from OSAS settles every applicant at once, up to the :count slot.|One pass list from OSAS settles every applicant at once, up to the :count slots.', $scholarship->slots, ['count' => $scholarship->slots]) }}
                    </x-cycle-step>
                </ol>
            </section>

            <section aria-labelledby="about-heading" class="mt-12">
                <h2 id="about-heading" class="text-base font-semibold text-gray-900">{{ __('About this scholarship') }}</h2>
                <p class="mt-3 max-w-prose whitespace-pre-line text-sm leading-relaxed text-gray-700">{{ $scholarship->description }}</p>
                @if ($scholarship->stem_courses_only)
                    <p class="mt-4 max-w-prose text-sm font-medium text-gray-900">{{ __('Open only to students in a STEM course.') }}</p>
                    <x-stem-courses class="mt-2" />
                @endif
            </section>

            @if ($documentCount > 0)
                <section aria-labelledby="documents-heading" class="mt-12">
                    <h2 id="documents-heading" class="text-base font-semibold text-gray-900">{{ __('Documents you will need to upload') }}</h2>

                    <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
                        @foreach ($scholarship->requiredDocuments as $document)
                            <li class="flex flex-col gap-3 px-3 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                                <div class="min-w-0 text-sm">
                                    <p class="font-medium text-gray-900">{{ $document->name }}</p>
                                    @if ($document->description)
                                        <p class="mt-1 text-gray-500">{{ $document->description }}</p>
                                    @endif
                                </div>
                                @if ($document->hasSample())
                                    <x-sample-preview :document="$document" class="w-full shrink-0 sm:w-64" />
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
