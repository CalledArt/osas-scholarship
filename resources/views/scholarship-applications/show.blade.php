{{--
    One application, set out as the cycle the sign-in page promises: when OSAS
    posted the scholarship, when the student applied, the review, and the
    decision. The decision is the stage lit, since it is what the student came
    to read, and while it is pending it takes the Stake role — waiting on a
    human decision is exactly what Stake marks. The scholarship's held-slot
    count is eager loaded with it, so availability() spends no extra round trip.
    Drawn like the other pages (asked 2026-10-25): the dashboards' banner, the
    title block, then the cycle and the documents as bordered panels, side by
    side from lg.
--}}
@php
    $scholarship = $application->scholarship;
    $limit = \App\Models\User::SCHOLARSHIP_LIMIT;
    $status = $application->status;
    $hasEnded = $scholarship->status === \App\Enums\ScholarshipStatus::Ended;
    $isPending = $status === \App\Enums\ApplicationStatus::Pending;

    // Whole calendar days, counted between day boundaries like every other
    // elapsed figure in the product.
    $waited = $application->submitted_at->copy()->startOfDay()->diffInDays(now()->startOfDay());

    // The register's only job is order, so a posting date that does not precede
    // the application is not printed as one. Imported and seeded records can
    // both produce it.
    $postedDate = $scholarship->created_at && $scholarship->created_at->lte($application->submitted_at)
        ? $scholarship->created_at
        : null;

    $decideTone = match ($status) {
        \App\Enums\ApplicationStatus::Approved => 'plot',
        \App\Enums\ApplicationStatus::Rejected => 'oxide',
        \App\Enums\ApplicationStatus::Voided => null,
        default => 'stake',
    };
@endphp

<x-app-layout :title="$scholarship->title" :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-back-link :href="route('scholarship-applications.index')">{{ __('Back to my applications') }}</x-back-link>

            <div class="mt-4">
                <x-welcome-banner :heading="$scholarship->title" :vt-title="true">
                    <x-badge class="me-1" :color="match ($status) {
                        \App\Enums\ApplicationStatus::Approved => 'green',
                        \App\Enums\ApplicationStatus::Rejected => 'red',
                        \App\Enums\ApplicationStatus::Voided => 'gray',
                        default => 'gold',
                    }">{{ $status->label() }}</x-badge>
                    {{ $scholarship->type->label() }}

                    <x-slot:action>
                        <a href="{{ route('scholarships.show', $scholarship) }}" class="inline-flex shrink-0 items-center self-start rounded-sheet border border-rule-strong bg-white px-4 py-2 font-semibold text-graphite shadow-seat transition duration-150 ease-in-out hover:bg-linen-sunk focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2 sm:self-auto">
                            {{ __('View the scholarship') }}
                        </a>
                    </x-slot:action>
                </x-welcome-banner>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-sheet border border-grant/30 bg-grant-wash px-4 py-3 text-grant-deep" role="status">
                    {{ session('status') }}
                </div>
            @endif

            {{-- The record's own fixed furniture: its reference first, because
                 that is what a student quotes when they phone the office. --}}
            <x-title-block class="mt-6">
                <x-field :label="__('Reference')" :mono="true">{{ $application->reference }}</x-field>
                <x-field :label="__('Applied')">
                    <time datetime="{{ $application->submitted_at->toDateString() }}">{{ $application->submitted_at->format('M j, Y') }}</time>
                </x-field>
                <x-field :label="__('Documents sent')">{{ $application->documents->count() }}</x-field>
                <x-field :label="__('Standing')">{{ $status->label() }}</x-field>
            </x-title-block>

            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_24rem] xl:grid-cols-[minmax(0,1fr)_28rem] lg:items-start">
                <section aria-labelledby="cycle-heading" class="overflow-hidden rounded-lg border border-rule bg-white">
                    <h2 id="cycle-heading" class="flex items-center gap-2.5 px-6 py-4 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 4v16M6 6h9l-2 3 2 3H6" />
                        </svg>
                        {{ __('Where your application stands') }}
                    </h2>

                    {{-- The title block above holds view-transition-name: cycle; a second element with it would cancel the transition. --}}
                    <ol class="divide-y divide-rule border-t border-rule">
                        <x-cycle-step :step="__('Post')" :date="$postedDate">
                            {{ trans_choice('OSAS posted this scholarship with :count slot.|OSAS posted this scholarship with :count slots.', $scholarship->slots, ['count' => $scholarship->slots]) }}
                        </x-cycle-step>

                        <x-cycle-step :step="__('Apply')" :date="$application->submitted_at">
                            @if ($application->documents->isEmpty())
                                {{ __('You applied. No documents were required.') }}
                            @else
                                {{ trans_choice('You sent :count document.|You sent :count documents.', $application->documents->count(), ['count' => $application->documents->count()]) }}
                            @endif
                        </x-cycle-step>

                        <x-cycle-step :step="__('Review')">
                            @if ($isPending)
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
                            @elseif ($status === \App\Enums\ApplicationStatus::Voided)
                                {{ __('Your application left review when you went on leave of absence (LOA).') }}
                            @else
                                {{ __('OSAS reviewed every applicant and the documents they sent.') }}
                            @endif
                        </x-cycle-step>

                        <x-cycle-step :step="__('Decide')" :tone="$decideTone" :current="true">
                            @switch($status)
                                @case(\App\Enums\ApplicationStatus::Approved)
                                    <p class="text-lead font-semibold leading-snug text-grant-deep">{{ __('You were approved') }}</p>
                                    <p class="mt-1">
                                        @if ($hasEnded)
                                            {{ __('You were on the pass list. The grant period has since ended, so it is no longer your active scholarship and you can apply to others again.') }}
                                        @else
                                            {{ __('You were on the pass list. This is your active scholarship: a student can hold only one at a time, so you cannot apply to others until OSAS ends its grant period.') }}
                                        @endif
                                    </p>
                                    @break

                                @case(\App\Enums\ApplicationStatus::Rejected)
                                    <p class="text-lead font-semibold leading-snug text-oxide-deep">{{ __('You were not approved') }}</p>
                                    @if ($application->passed_while_holding_active)
                                        <p class="mt-1">{{ __('Your name was on the pass list, but you already held an active scholarship, and a student can hold only one at a time. This application no longer counts toward your limit.') }}</p>
                                    @else
                                        <p class="mt-1">{{ __('Your name was not on the pass list. This application no longer counts toward the :limit applications you can have.', ['limit' => $limit]) }}</p>
                                    @endif
                                    @break

                                @case(\App\Enums\ApplicationStatus::Voided)
                                    <p class="text-lead font-semibold leading-snug text-ink">{{ __('Voided') }}</p>
                                    <p class="mt-1">{{ __('OSAS put you on leave of absence (LOA) before this application was decided, so it gave up its slot for another student. It no longer counts toward the :limit applications you can have.', ['limit' => $limit]) }}</p>
                                    @break

                                @default
                                    {{-- The wait, stated. The office can already see
                                         it in the decision queue; the student is the
                                         one who needs it. --}}
                                    <p class="text-lead font-semibold leading-snug text-ink">{{ __('Waiting for a decision') }}</p>
                                    <p class="mt-2">
                                        <span class="align-baseline text-figure font-bold text-ink">{{ $waited }}</span>
                                        <span class="font-annot text-annot uppercase text-slate">{{ trans_choice('day waiting|days waiting', $waited) }}</span>
                                    </p>
                                    <p class="mt-2">{{ __('One pass list from OSAS settles every applicant at once, up to the slots available. Until then, this application counts toward the :limit applications you can have.', ['limit' => $limit]) }}</p>
                            @endswitch

                            {{-- A refusal is not the end of a path: while the
                                 student can still apply, point them on. --}}
                            @if (in_array($status, [\App\Enums\ApplicationStatus::Rejected, \App\Enums\ApplicationStatus::Voided], true) && $remainingPlaces > 0)
                                <p class="mt-3">
                                    <span class="font-semibold text-ink">{{ __('You can still apply to other scholarships.') }}</span>
                                    <a href="{{ route('scholarships.index') }}" class="ml-1 rounded-sheet font-semibold text-seal underline underline-offset-4 hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">
                                        {{ __('Browse scholarships') }} &rarr;
                                    </a>
                                </p>
                            @endif
                        </x-cycle-step>
                    </ol>
                </section>

                <section aria-labelledby="documents-heading" class="overflow-hidden rounded-lg border border-rule bg-white">
                    <h2 id="documents-heading" class="flex items-center gap-2.5 px-6 py-4 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7l-4-4Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v4h4M9 12h6M9 16h6" />
                        </svg>
                        {{ __('Documents you sent') }}
                    </h2>

                    {{-- A heading never stands over an empty container: an empty box
                         under "Documents you sent" reads as lost paperwork, which is
                         the worst thing this page could imply. --}}
                    <ul class="divide-y divide-rule border-t border-rule">
                        @forelse ($application->documents as $document)
                            <li class="flex items-center gap-4 px-6 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-ink">{{ $document->requiredDocument->name }}</p>
                                    <p class="truncate text-slate">{{ $document->original_name }}</p>
                                </div>
                                <a href="{{ route('scholarship-applications.documents.show', [$application, $document]) }}" download class="shrink-0 rounded-sheet font-semibold text-seal hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">
                                    {{ __('Download') }}<span class="sr-only"> {{ $document->requiredDocument->name }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="px-6 py-6 text-slate">
                                {{ __('This scholarship required no documents, so there was nothing to send.') }}
                            </li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
