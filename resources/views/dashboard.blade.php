{{--
    The student's dashboard: the welcome banner the admin dashboard uses, then
    two panes of boxes: "Mine" on the left, what they hold and where they
    stand, and "Open" on the right, what they can still apply to. My applications closes the Mine column on wide screens; on
    a phone it follows the open scholarships, so the student's record and what
    they can act on come first. It leaves out an active scholarship, which
    the Active box above already shows. The rows are auto then 1fr, so a long Open
    pane gives its extra height to the second row instead of opening a gap
    under the first. With nothing open to apply to, the Open pane would be one
    sentence beside a tall column, so the panes stack instead: the Mine boxes
    run three across from xl, and the empty note and My applications span the
    width beneath them.
--}}
<x-app-layout :title="__('Dashboard')">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            {{-- What waits on the student comes first: a decision, then scholarships they can apply to. --}}
            <x-welcome-banner :heading="__('Welcome back, :name!', ['name' => mb_strtoupper(Auth::user()->surname_first_name)])">
                @if ($waitingApplicationCount > 0)
                    <span class="font-semibold tabular-nums text-ink">{{ $waitingApplicationCount }}</span>
                    {{ trans_choice('application is waiting for a decision.|applications are waiting for a decision.', $waitingApplicationCount) }}
                @endif
                @if ($hasOpenScholarships)
                    <span class="font-semibold tabular-nums text-ink">{{ $availableScholarshipCount }}</span>
                    {{ trans_choice('scholarship is open to you.|scholarships are open to you.', $availableScholarshipCount) }}
                @elseif ($waitingApplicationCount === 0)
                    {{ __("You're all caught up.") }}
                @endif

                <x-slot:corner>
                    <x-notification-bell :notifications="$notifications" />
                </x-slot:corner>
            </x-welcome-banner>

            <div @class([
                'mt-6 grid gap-6',
                'lg:grid-cols-[22rem_minmax(0,1fr)] lg:grid-rows-[auto_1fr] lg:items-start' => $hasOpenScholarships,
            ])>
                <section aria-labelledby="standing-heading" @class(['lg:col-start-1 lg:row-start-1' => $hasOpenScholarships])>
                    <h2 id="standing-heading" class="text-lead font-semibold text-ink">{{ __('Where you stand') }}</h2>

                    <div @class([
                        'mt-4 grid gap-4 sm:grid-cols-2',
                        'lg:grid-cols-1' => $hasOpenScholarships,
                        'xl:grid-flow-col xl:grid-cols-none xl:auto-cols-fr' => ! $hasOpenScholarships,
                    ])>
                        {{-- Course, year level and student number are kept by OSAS,
                             so they are stated here and corrected by the office,
                             never edited on the profile. --}}
                        <section aria-labelledby="student-heading" @class([
                            'border border-rule bg-white shadow-seat sm:col-span-2',
                            'lg:col-span-1' => $hasOpenScholarships,
                            'xl:col-span-1' => ! $hasOpenScholarships,
                        ])>
                            <div class="px-5 py-4">
                                <h3 id="student-heading" class="font-semibold uppercase text-ink">{{ Auth::user()->surname_first_name }}</h3>
                                <p class="mt-0.5 text-slate">{{ Auth::user()->course ?? __('Course not set') }}</p>
                            </div>
                            <dl class="grid grid-cols-2 border-t border-rule">
                                <div class="px-5 py-2.5">
                                    <dt class="font-annot text-annot uppercase text-slate">{{ __('Student number') }}</dt>
                                    <dd class="font-mono text-ink">{{ Auth::user()->student_number ?? '—' }}</dd>
                                </div>
                                <div class="border-l border-rule px-5 py-2.5">
                                    <dt class="font-annot text-annot uppercase text-slate">{{ __('Year level') }}</dt>
                                    <dd class="text-ink">{{ Auth::user()->year_level ? __('Year :level', ['level' => Auth::user()->year_level]) : '—' }}</dd>
                                </div>
                            </dl>
                        </section>

                        <x-active-scholarships :applications="$activeApplications" :student="Auth::user()" />
                    </div>
                </section>

                <x-available-scholarships :scholarships="$availableScholarships" :count="$availableScholarshipCount" :remaining-places="$remainingPlaces" :active-limit-reached="$activeLimitReached" :on-leave="$onLeave" :class="$hasOpenScholarships ? 'lg:col-start-2 lg:row-span-2 lg:row-start-1' : null">
                    @if ($availableScholarshipCount > $availableScholarships->count())
                        <a href="{{ route('scholarships.index') }}"
                           class="mt-4 inline-flex items-center gap-1.5 rounded-sheet font-semibold text-seal underline-offset-2 hover:text-seal-deep hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2 focus-visible:ring-offset-linen">
                            {{ __('View all :count available scholarships', ['count' => $availableScholarshipCount]) }}
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10h12m-5-5 5 5-5 5" /></svg>
                        </a>
                    @endif
                </x-available-scholarships>

                @if ($applications->isNotEmpty())
                    <section aria-labelledby="applications-heading" @class(['lg:col-start-1 lg:row-start-2' => $hasOpenScholarships])>
                        <h2 id="applications-heading" class="text-lead font-semibold text-ink">{{ __('My applications') }}</h2>

                        <ul @class([
                            'mt-3 grid gap-3 sm:grid-cols-2',
                            'lg:grid-cols-1' => $hasOpenScholarships,
                            'xl:grid-cols-3' => ! $hasOpenScholarships,
                        ]) role="list">
                            @foreach ($applications as $application)
                                @php($waited = $application->status === \App\Enums\ApplicationStatus::Pending ? (int) $application->submitted_at->copy()->startOfDay()->diffInDays(now()->startOfDay()) : null)
                                <li>
                                    {{-- The student's own application, still waiting on OSAS,
                                         takes the Gold Wash: Citation Gold means yours and waiting. --}}
                                    <a href="{{ route('scholarship-applications.show', $application) }}" @class([
                                        'group block border px-5 py-3.5 shadow-seat transition duration-150 ease-in-out hover:border-seal focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2 focus-visible:ring-offset-linen',
                                        'border-gold-deep bg-gold-wash' => $waited !== null,
                                        'border-rule bg-white' => $waited === null,
                                    ])>
                                        <p data-vt-title class="font-semibold text-ink [overflow-wrap:anywhere] group-hover:text-seal">{{ $application->scholarship_title }}</p>
                                        <div class="mt-1.5 flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                                            <p @class(['text-ink' => $waited !== null, 'text-slate' => $waited === null])>
                                                <span class="font-mono">{{ $application->reference }}</span>
                                                @if ($waited !== null)
                                                    &middot; {{ trans_choice(':count day waiting|:count days waiting', $waited, ['count' => $waited]) }}
                                                @endif
                                            </p>
                                            <x-badge class="shrink-0" :color="match ($application->status->value) {
                                                'approved' => 'green',
                                                'rejected' => 'red',
                                                'voided' => 'gray',
                                                default => 'paper',
                                            }">
                                                {{ $application->status->label() }}
                                            </x-badge>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if ($applicationCount > $applications->count())
                            <a href="{{ route('scholarship-applications.index') }}"
                               class="mt-4 inline-flex items-center gap-1.5 rounded-sheet font-semibold text-seal underline-offset-2 hover:text-seal-deep hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2 focus-visible:ring-offset-linen">
                                {{ __('View all :count applications', ['count' => $applicationCount]) }}
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10h12m-5-5 5 5-5 5" /></svg>
                            </a>
                        @endif
                    </section>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
