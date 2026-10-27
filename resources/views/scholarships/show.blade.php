{{--
    A scholarship as the student meets it: its parcel and standing at a glance,
    then the cycle, with the stage the student's attention belongs on lit.

    The standing states the binding reason first and shows the deadline only
    while applications are actually open. A deadline on its own reads as open on
    a scholarship that is full or that OSAS closed early — which is what
    x-availability exists to prevent.
--}}
@php
    use App\Enums\ApplicationStatus;
    use App\Enums\ScholarshipAvailability;
    use App\Enums\ScholarshipStatus;

    $availability = $scholarship->availability();
    $isOpen = $availability === ScholarshipAvailability::Open;
    $hasEnded = $scholarship->status === ScholarshipStatus::Ended;
    // Open to others, but this student is on leave of absence, already holds
    // an active scholarship and may hold only one, or already holds a place on
    // another posting of this scholarship's preset.
    $isRestricted = $isOpen && ! $application && ($onLeave || $activeLimitReached || $holdsPreset);
    $documentCount = $scholarship->requiredDocuments->count();

    $standing = match (true) {
        $isRestricted => __('Restricted'),
        $hasEnded => __('Grant period ended'),
        $availability === ScholarshipAvailability::ClosedByOsas => __('Closed by OSAS'),
        $availability === ScholarshipAvailability::PastDeadline => __('Past deadline'),
        $availability === ScholarshipAvailability::Full => __('Full'),
        default => __('Open'),
    };

    $decided = $application !== null && $application->status !== ApplicationStatus::Pending;

    $decideTone = match (true) {
        $application === null => null,
        $application->status === ApplicationStatus::Approved => 'plot',
        $application->status === ApplicationStatus::Rejected => 'oxide',
        $application->status === ApplicationStatus::Voided => null,
        default => 'stake',
    };
@endphp

<x-app-layout :title="$scholarship->title" :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-leaf">
            <a href="{{ route('scholarships.index') }}" class="rounded-sheet font-medium text-seal underline underline-offset-4 hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Back to scholarships') }}</a>

            <h1 data-vt-title class="mt-6 text-lead font-semibold text-ink [overflow-wrap:anywhere]">{{ $scholarship->title }}</h1>

            <div class="mt-5">
                <x-badge :color="$isOpen && ! $isRestricted ? 'green' : 'gray'">{{ $standing }}</x-badge>
            </div>

            <x-title-block class="-mx-6 mt-5 sm:mx-0">
                <x-field :label="__('Slots')">{{ $scholarship->slots }}</x-field>

                <x-field :label="$isOpen ? __('Closes') : __('Closed')">
                    @if ($scholarship->deadline)
                        <time datetime="{{ $scholarship->deadline->toDateString() }}">{{ $scholarship->deadline->format('M j, Y') }}</time>
                    @else
                        <span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No deadline') }}</span>
                    @endif
                </x-field>

                <x-field :label="__('Type')">{{ __(':type scholarship', ['type' => $scholarship->type->label()]) }}</x-field>

                <x-field :label="__('Standing')">
                    @if ($isOpen && ! $isRestricted)
                        <x-deadline :scholarship="$scholarship" :urgent="true" />
                    @else
                        {{ $standing }}
                    @endif
                </x-field>
            </x-title-block>

            <x-season :scholarship="$scholarship" class="mt-4" />

            <section aria-labelledby="cycle-heading" class="mt-10">
                <h2 id="cycle-heading" class="sr-only">{{ __('How this scholarship is decided') }}</h2>

                <ol class="-mx-6 divide-y divide-rule border-y border-rule [view-transition-name:cycle]">
                    <x-cycle-step :step="__('Post')" :date="$scholarship->created_at">
                        {{ trans_choice('OSAS posted this scholarship with :count slot.|OSAS posted this scholarship with :count slots.', $scholarship->slots, ['count' => $scholarship->slots]) }}
                    </x-cycle-step>

                    <x-cycle-step :step="__('Apply')" :date="$application?->submitted_at" :current="$isOpen">
                        @if ($application)
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                <span class="text-ink">{{ __('You applied.') }}</span>
                                <span class="font-annot text-annot uppercase text-slate">{{ __('Ref') }} <span class="font-mono normal-case tracking-normal text-ink">{{ $application->reference }}</span></span>
                            </div>
                            <a href="{{ route('scholarship-applications.show', $application) }}" class="mt-2 inline-block rounded-sheet font-semibold text-seal hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">
                                {{ __('View your application') }} &rarr;
                            </a>
                        @elseif ($isRestricted && $onLeave)
                            {{ __('You are on leave of absence (LOA), so you can\'t apply to scholarships. You can apply again once you return to your studies and OSAS marks you active.') }}
                        @elseif ($isRestricted && $activeLimitReached)
                            {{ __('You currently hold an active scholarship. As a student may hold only one active scholarship at a time, applications to other scholarships are restricted until the grant period of your current scholarship has ended.') }}
                        @elseif ($isRestricted)
                            {{ __('You already applied to another :title posting. A student may hold only one at a time, so you can apply here only if that application is rejected or its grant period ends.', ['title' => \App\Support\ScholarshipPresets::ALL[$scholarship->preset]['title'] ?? $scholarship->title]) }}
                        @elseif ($isOpen && ! $admitsCourse)
                            {{ __('Only students in a STEM course can apply, and your registered course, :course, is not one of them.', ['course' => auth()->user()->course ?? __('not set')]) }}
                            <p class="mt-1 text-slate">{{ __('Course, year level and student number are kept by OSAS. Email the office if one of them is wrong.') }}</p>
                        @elseif ($isOpen && $remainingPlaces > 0)
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-ink">
                                        @if ($documentCount > 0)
                                            {{ trans_choice('Send the :count document listed below.|Send the :count documents listed below.', $documentCount, ['count' => $documentCount]) }}
                                        @else
                                            {{ __('No documents are needed to apply.') }}
                                        @endif
                                        @if ($scholarship->deadline)
                                            {{ __('Applications close on :date.', ['date' => $scholarship->deadline->format('F j, Y')]) }}
                                        @endif
                                    </p>
                                </div>
                                <a href="{{ route('scholarships.apply', $scholarship) }}" class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-sheet border border-transparent bg-seal px-4 py-2.5 font-semibold text-white shadow-seat transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 sm:self-auto">
                                    {{ __('Apply now') }}
                                </a>
                            </div>
                        @elseif ($isOpen)
                            {{ __('You already have :limit applications waiting for a decision. You can apply again once one of them is rejected.', ['limit' => \App\Models\User::SCHOLARSHIP_LIMIT]) }}
                        @else
                            {{ __('This scholarship is no longer accepting applications.') }}
                        @endif
                    </x-cycle-step>

                    <x-cycle-step :step="__('Review')">
                        {{ __('OSAS reviews every applicant and the documents they sent.') }}
                    </x-cycle-step>

                    {{-- Where the student's own outcome here is already known, the
                         register says what it was instead of reciting the generic
                         procedure above it. --}}
                    <x-cycle-step :step="__('Decide')" :tone="$decideTone" :current="! $isOpen">
                        @if ($application?->status === ApplicationStatus::Voided)
                            <span class="text-ink">{{ __('Your application was voided when OSAS put you on leave of absence (LOA) before it was decided, freeing its slot.') }}</span>
                            <x-badge class="ml-2">{{ $application->status->label() }}</x-badge>
                        @elseif ($decided)
                            <span class="text-ink">
                                @if ($application->status === ApplicationStatus::Approved)
                                    {{ __('You were approved.') }}
                                @else
                                    {{ __('You were not approved.') }}
                                @endif
                            </span>
                            <x-badge class="ml-2" :color="$application->status === ApplicationStatus::Approved ? 'green' : 'red'">{{ $application->status->label() }}</x-badge>
                        @elseif ($application)
                            <span class="text-ink">{{ __('Waiting for a decision.') }}</span>
                            <x-badge class="ml-2" color="gold">{{ $application->status->label() }}</x-badge>
                            @if ($scholarship->deadline && ! $scholarship->hasPassedDeadline())
                                <p class="mt-1 text-slate">{{ __('Nothing is decided until applications close on :date.', ['date' => $scholarship->deadline->format('F j, Y')]) }}</p>
                            @endif
                        @else
                            {{ trans_choice('One pass list from OSAS settles every applicant at once, up to the :count slot.|One pass list from OSAS settles every applicant at once, up to the :count slots.', $scholarship->slots, ['count' => $scholarship->slots]) }}
                        @endif
                    </x-cycle-step>
                </ol>
            </section>

            <section aria-labelledby="about-heading" class="mt-12">
                <h2 id="about-heading" class="text-lead font-semibold text-ink">{{ __('About this scholarship') }}</h2>
                <p class="mt-3 whitespace-pre-line leading-relaxed text-graphite">{{ $scholarship->description }}</p>
                @if ($scholarship->stem_courses_only)
                    <p class="mt-4 font-medium text-ink">{{ __('Open only to students in a STEM course.') }}</p>
                    @if ($admitsCourse && auth()->user()->course)
                        <p class="mt-1 text-grant-deep">{{ __('Your course, :course, qualifies.', ['course' => auth()->user()->course]) }}</p>
                    @endif
                    <x-stem-courses class="mt-2" />
                @endif
            </section>

            @if ($documentCount > 0)
                <section aria-labelledby="documents-heading" class="mt-12">
                    <h2 id="documents-heading" class="text-lead font-semibold text-ink">
                        {{ $isOpen ? __('Documents you will need to upload') : __('Documents this scholarship required') }}
                    </h2>

                    <ul class="-mx-6 mt-3 divide-y divide-rule border-y border-rule">
                        @foreach ($scholarship->requiredDocuments as $document)
                            <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                                <div class="min-w-0">
                                    <p class="font-medium text-ink">{{ $document->name }}</p>
                                    @if ($document->description)
                                        <p class="mt-1 text-slate">{{ $document->description }}</p>
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
