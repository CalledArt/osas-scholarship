{{--
    The review page, laid out in the order OSAS works it: where the scholarship
    stands, then the pass list (with the outcome of the last one just above
    it, since that is what produced it), then the applicants it decides. What OSAS posted (the description
    and required documents) is reference, so from 1024px it moves to a side
    column instead of pushing the work down; below that it follows the
    applicants, so source and focus order stay the visual order. Drawn like
    the other admin pages (asked 2026-10-25): the dashboards' banner carries
    the title, where it stands and its two actions, and each part of the work
    is a bordered panel headed like the dashboard's postings table. From lg the
    page fits the screen: the applicant list and the description each scroll
    inside their own panel, under headers that stay in view.
--}}
@php
    $pendingCount = $scholarship->applications->where('status', \App\Enums\ApplicationStatus::Pending)->count();
    $passedCount = $scholarship->applications->where('status', \App\Enums\ApplicationStatus::Approved)->count();
@endphp

<x-app-layout :title="$scholarship->title" :sheet="true">
    <div class="px-6 py-10 lg:flex lg:h-screen lg:flex-col lg:px-10 lg:py-6">
        <div class="mx-auto max-w-[100rem] lg:flex lg:min-h-0 lg:w-full lg:flex-col">
            <x-back-link :href="route('admin.scholarships.index')" class="lg:self-start">{{ __('Back to scholarships') }}</x-back-link>

            <div class="mt-4">
                <x-welcome-banner :heading="$scholarship->title" :vt-title="true">
                    <x-badge class="me-1" :color="$scholarship->status->value === 'open' ? 'green' : 'gray'">
                        {{ $scholarship->status->label() }}
                    </x-badge>
                    {{ $scholarship->type->label() }}
                    @if ($scholarship->stem_courses_only)
                        <span aria-hidden="true">&middot;</span> {{ __('STEM courses only') }}
                    @endif
                    <span class="tabular-nums"><span aria-hidden="true">&middot;</span> {{ $scholarship->remainingSlots() }} / {{ $scholarship->slots }} {{ __('slots remaining') }}</span>
                    @if ($scholarship->deadline)
                        <span class="tabular-nums"><span aria-hidden="true">&middot;</span> {{ $scholarship->hasPassedDeadline() ? __('Past deadline') : __('Deadline') }}: {{ $scholarship->deadline->format('M j, Y') }}</span>
                    @endif

                    <x-slot:action>
                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            @if ($passedCount > 0)
                                <a href="{{ route('admin.fund-allocation.show', $scholarship) }}" class="inline-flex items-center rounded-sheet border border-rule-strong bg-white px-4 py-2 font-semibold text-graphite shadow-seat transition duration-150 ease-in-out hover:bg-linen-sunk focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Fund Allocation') }}</a>
                            @endif
                            <a href="{{ route('admin.scholarships.edit', $scholarship) }}" class="inline-flex items-center rounded-sheet border border-rule-strong bg-white px-4 py-2 font-semibold text-graphite shadow-seat transition duration-150 ease-in-out hover:bg-linen-sunk focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Edit') }}</a>
                        </div>
                    </x-slot:action>
                </x-welcome-banner>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-sheet border border-grant/30 bg-grant-wash px-4 py-3  text-grant-deep" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-6 rounded-sheet border border-oxide/30 bg-oxide-wash px-4 py-3  text-oxide-deep" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mt-6 grid gap-6 lg:min-h-0 lg:flex-1 lg:grid-cols-[minmax(0,1fr)_20rem] lg:grid-rows-[minmax(0,1fr)] xl:grid-cols-[minmax(0,1fr)_26rem]">
                <div class="min-w-0 lg:flex lg:min-h-0 lg:flex-col">
                    @if (session('decisionResults'))
                        @php $results = session('decisionResults'); @endphp
                        <section aria-labelledby="results-heading" tabindex="0" class="mb-6 rounded-lg border border-rule bg-linen-sunk p-5 lg:max-h-[40vh] lg:shrink-0 lg:overflow-y-auto">
                            <h2 id="results-heading" class="text-lead font-semibold text-ink">{{ __('Pass List Results') }}</h2>
                            <p class="mt-1 tabular-nums text-graphite">
                                {{ __(':approved approved, :rejected rejected.', ['approved' => count($results['approved']), 'rejected' => count($results['rejected'])]) }}
                            </p>

                            @if (! empty($results['already_active']) || ! empty($results['skipped_capacity']) || ! empty($results['ambiguous']) || ! empty($results['unmatched']))
                                <div class="mt-4 divide-y divide-rule border-t border-rule">
                                    @if (! empty($results['already_active']))
                                        <div class="py-3">
                                            <p class="font-semibold text-ink">{{ __('Passed but rejected — already holds an active scholarship:') }}</p>
                                            <ul class="mt-1 list-inside list-disc text-graphite">
                                                @foreach ($results['already_active'] as $row)
                                                    <li><span class="uppercase">{{ $row['name'] }}</span> ({{ $row['email'] }}) — {{ __('holds :title', ['title' => $row['active_scholarship']]) }}</li>
                                                @endforeach
                                            </ul>
                                            <p class="mt-2 text-slate">{{ __('A student can hold only one active scholarship at a time. They were emailed why, and their slots stay open.') }}</p>
                                        </div>
                                    @endif

                                    @if (! empty($results['skipped_capacity']))
                                        <div class="py-3">
                                            <p class="font-semibold text-ink">{{ __('Matched but not approved — no slots remaining:') }}</p>
                                            <ul class="mt-1 list-inside list-disc text-graphite">
                                                @foreach ($results['skipped_capacity'] as $row)
                                                    <li><span class="uppercase">{{ $row['name'] }}</span> ({{ $row['email'] }})</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if (! empty($results['ambiguous']))
                                        <div class="py-3">
                                            <p class="font-semibold text-ink">{{ __('Left pending — name matches more than one applicant:') }}</p>
                                            <ul class="mt-1 list-inside list-disc text-graphite">
                                                @foreach ($results['ambiguous'] as $row)
                                                    <li><span class="uppercase">{{ $row['name'] }}</span> ({{ __(':count applicants', ['count' => $row['count']]) }})</li>
                                                @endforeach
                                            </ul>
                                            <p class="mt-2 text-slate">{{ __('Add their student numbers to the pass list and upload it again.') }}</p>
                                        </div>
                                    @endif

                                    @if (! empty($results['unmatched']))
                                        <div class="py-3">
                                            <p class="font-semibold text-ink">{{ __("Rows in the file that didn't match any pending applicant:") }}</p>
                                            <ul class="mt-1 list-inside list-disc text-graphite">
                                                @foreach ($results['unmatched'] as $row)
                                                    <li>
                                                        @if ($row['name'] !== null)<span class="uppercase">{{ $row['name'] }}</span>@else{{ __('(no name)') }}@endif
                                                        @if ($row['student_number'])
                                                            — <span class="tabular-nums">{{ $row['student_number'] }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </section>
                    @endif

                    <section aria-labelledby="pass-list-heading" class="rounded-lg border border-rule bg-white px-5 py-4">
                        <h2 id="pass-list-heading" class="flex items-center gap-2.5 text-lead font-semibold text-ink">
                            <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0-4 4m4-4 4 4M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3" />
                            </svg>
                            {{ __('Upload Pass List') }}
                        </h2>

                        @if ($pendingCount > 0)
                            <p class="mt-1 max-w-prose  text-slate">
                                {{ __('Upload a CSV of who passed — a "student_number" column, a "name" column, or both (a row with a student number is matched by that number only; a name is matched only on rows without one). A header-less single column of names also works. Every other pending applicant (:count) will be rejected automatically.', ['count' => $pendingCount]) }}
                            </p>

                            <form
 method="POST"
 action="{{ route('admin.scholarships.applications.import-decisions', $scholarship) }}"
 enctype="multipart/form-data"
 class="mt-4 flex flex-col gap-3 rounded-sheet border border-rule p-4 sm:flex-row sm:items-center"
 x-data="{ sending: false }"
                                @submit="if (! confirm('{{ __('This will approve matched names and reject every other pending applicant. Continue?') }}')) { $event.preventDefault(); return; } sending = true"
                                @pageshow.window="if ($event.persisted) sending = false"
                            >
                                @csrf
                                {{-- Deciding emails every applicant inside the request, so it can take a while: the button says so and refuses a second press. --}}
                                <x-file-input name="file" accept=".csv,.txt" class="min-w-0 flex-1" required aria-label="{{ __('Pass list CSV') }}" ::inert="sending" />
                                <x-secondary-button type="submit" class="shrink-0 justify-center" x-bind:disabled="sending">
                                    <span class="grid">
                                        <span class="col-start-1 row-start-1" :class="{ 'invisible': sending }">{{ __('Apply Decisions') }}</span>
                                        <span class="invisible col-start-1 row-start-1 inline-flex items-center justify-center gap-2" :class="{ '!visible': sending }">
                                            <svg class="h-4 w-4 shrink-0 animate-spin motion-reduce:animate-pulse" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            {{ __('Applying decisions...') }}
                                        </span>
                                    </span>
                                </x-secondary-button>
                            </form>
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        @else
                            <p class="mt-1  text-slate">
                                {{ __('There are no pending applicants to decide on right now.') }}
                            </p>
                        @endif
                    </section>

                    <section aria-labelledby="applicants-heading" class="mt-6 overflow-hidden rounded-lg border border-rule bg-white lg:flex lg:min-h-0 lg:flex-1 lg:flex-col">
                        <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 px-5 py-4">
                            <h2 id="applicants-heading" class="flex items-center gap-2.5 text-lead font-semibold text-ink">
                                <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <circle cx="9" cy="8" r="3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.5M17.5 19a5.5 5.5 0 0 0-2.5-4.6" />
                                </svg>
                                {{ __('Applicants') }} <span class="font-normal tabular-nums text-slate">({{ $scholarship->applications->count() }})</span>
                            </h2>
                            @if ($scholarship->applications->isNotEmpty())
                                <a href="{{ route('admin.scholarships.applications.report', $scholarship) }}" download class="rounded-sheet font-semibold text-seal underline underline-offset-4 hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Print report (PDF)') }}</a>
                            @endif
                        </div>

                        @if ($scholarship->applications->isEmpty())
                            <p class="border-t border-rule px-5 py-8 text-center text-slate">{{ __('No applications yet.') }}</p>
                        @else
                            {{-- Relative, so the sr-only labels in its rows stay inside the scroll instead of stretching the page. --}}
                            <ul tabindex="0" aria-labelledby="applicants-heading" class="relative divide-y divide-rule border-t border-rule lg:min-h-0 lg:overflow-y-auto focus-visible:-outline-offset-2">
                                @foreach ($scholarship->applications as $application)
                                    <li class="px-5 py-4 transition-colors duration-150 ease-in-out hover:bg-linen-sunk/60">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-ink [overflow-wrap:anywhere]">
                                                    <span class="uppercase">{{ $application->user->surname_first_name }}</span>
                                                    @if ($application->user->student_number)
                                                        <span class="ms-1 font-normal tabular-nums text-slate">({{ $application->user->student_number }})</span>
                                                    @endif
                                                </p>
                                                <p class="mt-1  text-slate [overflow-wrap:anywhere]">
                                                    {{ $application->user->email }}
                                                    @if ($application->user->course)
                                                        &middot; {{ $application->user->course }}
                                                    @endif
                                                </p>
                                            </div>
                                            <x-badge class="shrink-0" :color="match ($application->status->value) {
                                                'approved' => 'green',
                                                'rejected' => 'red',
                                                'voided' => 'gray',
                                                default => 'gold',
                                            }">
                                                {{ $application->status->label() }}
                                            </x-badge>
                                        </div>

                                        @if ($application->passed_while_holding_active)
                                            <p class="mt-1 text-slate">{{ __('Passed, but already held an active scholarship.') }}</p>
                                        @elseif ($application->status === \App\Enums\ApplicationStatus::Voided)
                                            <p class="mt-1 text-slate">{{ __('Voided when the student went on leave of absence (LOA); the slot was freed.') }}</p>
                                        @endif

                                        @if ($application->documents->isNotEmpty())
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($application->documents as $document)
                                                    <a href="{{ route('scholarship-applications.documents.show', [$application, $document]) }}" download class="inline-flex items-center gap-1 rounded-sheet border border-rule px-2.5 py-1  font-medium text-graphite transition-colors duration-150 ease-in-out hover:border-seal hover:text-seal focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">
                                                        {{ $document->requiredDocument->name }}<span class="sr-only">, {{ $application->user->surname_first_name }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>

                {{-- The reference beside the work: a long description scrolls inside it (capped on a phone, the column's height from lg) instead of stretching the page. --}}
                <aside aria-labelledby="about-heading" class="overflow-hidden rounded-lg border border-rule bg-white lg:flex lg:min-h-0 lg:flex-col">
                    <h2 id="about-heading" class="flex items-center gap-2.5 px-5 py-4 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5M12 8h.01" />
                        </svg>
                        {{ __('About this scholarship') }}
                    </h2>
                    <div tabindex="0" role="region" aria-labelledby="about-heading" class="relative max-h-96 overflow-y-auto border-t border-rule lg:max-h-none lg:min-h-0 lg:flex-1 focus-visible:-outline-offset-2">
                        <p class="max-w-prose whitespace-pre-line text-justify px-5 py-4 leading-relaxed text-graphite">{{ $scholarship->description }}</p>

                        @if ($scholarship->requiredDocuments->isNotEmpty())
                            <h3 class="border-t border-rule bg-linen-sunk px-5 py-2.5 font-semibold text-ink">{{ __('Required Documents') }}</h3>
                            <ul class="divide-y divide-rule border-t border-rule px-5">
                                @foreach ($scholarship->requiredDocuments as $document)
                                    <li class="flex flex-col gap-3 py-3 sm:flex-row sm:items-start sm:justify-between lg:flex-col lg:items-stretch">
                                        <p class="min-w-0  font-medium text-ink">{{ $document->name }}</p>
                                        @if ($document->hasSample())
                                            <x-sample-preview :document="$document" class="w-full shrink-0 sm:w-64 lg:w-full" />
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
