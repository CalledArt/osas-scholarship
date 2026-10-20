{{--
    The review page, laid out in the order OSAS works it: where the scholarship
    stands, then the pass list (with the outcome of the last one just above
    it, since that is what produced it), then the applicants it decides. What OSAS posted (the description
    and required documents) is reference, so from 1024px it moves to a side
    column instead of pushing the work down; below that it follows the
    applicants, so source and focus order stay the visual order.
--}}
@php
    $pendingCount = $scholarship->applications->where('status', \App\Enums\ApplicationStatus::Pending)->count();
@endphp

<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <a href="{{ route('admin.scholarships.index') }}" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Back to scholarships') }}</a>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
                <h1 data-vt-title class="min-w-0 text-xl font-semibold leading-tight text-gray-900 [overflow-wrap:anywhere]">{{ $scholarship->title }}</h1>
                <a href="{{ route('admin.scholarships.edit', $scholarship) }}" class="inline-flex shrink-0 items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Edit') }}</a>
            </div>

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                <x-badge :color="$scholarship->status->value === 'open' ? 'green' : 'gray'">
                    {{ $scholarship->status->label() }}
                </x-badge>
                <span>{{ $scholarship->type->label() }}</span>
                @if ($scholarship->stem_courses_only)
                    <span><span aria-hidden="true">&middot;</span> {{ __('STEM courses only') }}</span>
                @endif
                <span class="tabular-nums"><span aria-hidden="true">&middot;</span> {{ $scholarship->remainingSlots() }} / {{ $scholarship->slots }} {{ __('slots remaining') }}</span>
                @if ($scholarship->deadline)
                    <span class="tabular-nums"><span aria-hidden="true">&middot;</span> {{ __('Deadline') }}: {{ $scholarship->deadline->format('M j, Y') }}</span>
                @endif
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mt-10 lg:grid lg:grid-cols-[minmax(0,1fr)_18rem] lg:gap-x-12">
                <div class="min-w-0">
                    @if (session('decisionResults'))
                        @php $results = session('decisionResults'); @endphp
                        <section aria-labelledby="results-heading" class="mb-10 rounded-lg border border-gray-200 bg-gray-50 p-5 text-sm">
                            <h2 id="results-heading" class="text-base font-semibold text-gray-900">{{ __('Pass List Results') }}</h2>
                            <p class="mt-1 tabular-nums text-gray-700">
                                {{ __(':approved approved, :rejected rejected.', ['approved' => count($results['approved']), 'rejected' => count($results['rejected'])]) }}
                            </p>

                            @if (! empty($results['skipped_capacity']) || ! empty($results['ambiguous']) || ! empty($results['unmatched']))
                                <div class="mt-4 divide-y divide-gray-200 border-t border-gray-200">
                                    @if (! empty($results['skipped_capacity']))
                                        <div class="py-3">
                                            <p class="font-semibold text-gray-900">{{ __('Matched but not approved — no slots remaining:') }}</p>
                                            <ul class="mt-1 list-inside list-disc text-gray-600">
                                                @foreach ($results['skipped_capacity'] as $row)
                                                    <li>{{ $row['name'] }} ({{ $row['email'] }})</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if (! empty($results['ambiguous']))
                                        <div class="py-3">
                                            <p class="font-semibold text-gray-900">{{ __('Left pending — name matches more than one applicant:') }}</p>
                                            <ul class="mt-1 list-inside list-disc text-gray-600">
                                                @foreach ($results['ambiguous'] as $row)
                                                    <li>{{ $row['name'] }} ({{ __(':count applicants', ['count' => $row['count']]) }})</li>
                                                @endforeach
                                            </ul>
                                            <p class="mt-2 text-gray-500">{{ __('Add their student numbers to the pass list and upload it again.') }}</p>
                                        </div>
                                    @endif

                                    @if (! empty($results['unmatched']))
                                        <div class="py-3">
                                            <p class="font-semibold text-gray-900">{{ __("Rows in the file that didn't match any pending applicant:") }}</p>
                                            <ul class="mt-1 list-inside list-disc text-gray-600">
                                                @foreach ($results['unmatched'] as $row)
                                                    <li>
                                                        {{ $row['name'] ?? __('(no name)') }}
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

                    <section aria-labelledby="pass-list-heading">
                        <h2 id="pass-list-heading" class="text-base font-semibold text-gray-900">{{ __('Upload Pass List') }}</h2>

                        @if ($pendingCount > 0)
                            <p class="mt-1 max-w-prose text-sm text-gray-500">
                                {{ __('Upload a CSV of who passed — a "student_number" column, a "name" column, or both (student number is matched first since it\'s unique; name is the fallback). A header-less single column of names also works. Every other pending applicant (:count) will be rejected automatically.', ['count' => $pendingCount]) }}
                            </p>

                            <form
                                method="POST"
                                action="{{ route('admin.scholarships.applications.import-decisions', $scholarship) }}"
                                enctype="multipart/form-data"
                                class="mt-4 flex flex-col gap-3 rounded-lg border border-gray-200 p-4 sm:flex-row sm:items-center"
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
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('There are no pending applicants to decide on right now.') }}
                            </p>
                        @endif
                    </section>

                    <section aria-labelledby="applicants-heading" class="mt-12">
                        <h2 id="applicants-heading" class="text-base font-semibold text-gray-900">
                            {{ __('Applicants') }} <span class="font-normal tabular-nums text-gray-500">({{ $scholarship->applications->count() }})</span>
                        </h2>

                        @if ($scholarship->applications->isEmpty())
                            <p class="mt-3 border-y border-gray-200 py-6 text-sm text-gray-500">{{ __('No applications yet.') }}</p>
                        @else
                            <ul class="mt-3 divide-y divide-gray-100 border-y border-gray-200">
                                @foreach ($scholarship->applications as $application)
                                    <li class="py-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere]">
                                                    {{ $application->user->name }}
                                                    @if ($application->user->student_number)
                                                        <span class="ms-1 font-normal tabular-nums text-gray-500">({{ $application->user->student_number }})</span>
                                                    @endif
                                                </p>
                                                <p class="mt-1 text-sm text-gray-500 [overflow-wrap:anywhere]">
                                                    {{ $application->user->email }}
                                                    @if ($application->user->course)
                                                        &middot; {{ $application->user->course }}
                                                    @endif
                                                </p>
                                            </div>
                                            <x-badge class="shrink-0" :color="match ($application->status->value) {
                                                'approved' => 'green',
                                                'rejected' => 'red',
                                                default => 'gold',
                                            }">
                                                {{ $application->status->label() }}
                                            </x-badge>
                                        </div>

                                        @if ($application->documents->isNotEmpty())
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($application->documents as $document)
                                                    <a href="{{ route('scholarship-applications.documents.show', [$application, $document]) }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-600 transition-colors duration-150 ease-in-out hover:border-cvsu-green hover:text-cvsu-green focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">
                                                        {{ $document->requiredDocument->name }}<span class="sr-only">, {{ $application->user->name }}</span>
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

                <aside aria-labelledby="about-heading" class="mt-12 lg:mt-0">
                    <h2 id="about-heading" class="text-base font-semibold text-gray-900">{{ __('About this scholarship') }}</h2>
                    <p class="mt-3 max-w-prose whitespace-pre-line text-sm leading-relaxed text-gray-700">{{ $scholarship->description }}</p>

                    @if ($scholarship->requiredDocuments->isNotEmpty())
                        <h3 class="mt-8 text-sm font-semibold text-gray-900">{{ __('Required Documents') }}</h3>
                        <ul class="mt-2 divide-y divide-gray-100 border-y border-gray-200">
                            @foreach ($scholarship->requiredDocuments as $document)
                                <li class="flex flex-col gap-3 py-3 sm:flex-row sm:items-start sm:justify-between lg:flex-col lg:items-stretch">
                                    <p class="min-w-0 text-sm font-medium text-gray-900">{{ $document->name }}</p>
                                    @if ($document->hasSample())
                                        <x-sample-preview :document="$document" class="w-full shrink-0 sm:w-64 lg:w-full" />
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
