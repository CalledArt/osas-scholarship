<x-app-layout :title="__('Dashboard')" :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('Welcome back, :name!', ['name' => mb_strtoupper(Auth::user()->surname_first_name)])">
                @if ($readyForPassList->isNotEmpty())
                    <span class="font-semibold tabular-nums text-ink">{{ $readyForPassList->count() }}</span>
                    {{ trans_choice('scholarship is ready for a pass list.|scholarships are ready for a pass list.', $readyForPassList->count()) }}
                    @if ($takingApplications->isNotEmpty())
                        <span class="font-semibold tabular-nums text-ink">{{ $takingApplications->count() }}</span>
                        {{ trans_choice('more is still taking applications.|more are still taking applications.', $takingApplications->count()) }}
                    @endif
                @elseif ($takingApplications->isNotEmpty())
                    {{ __('No pass lists are due yet.') }}
                    <span class="font-semibold tabular-nums text-ink">{{ $takingApplications->count() }}</span>
                    {{ trans_choice('scholarship with applicants is still taking applications.|scholarships with applicants are still taking applications.', $takingApplications->count()) }}
                @elseif ($scholarshipCount === 0)
                    {{ __('No scholarships have been posted yet.') }}
                @else
                    {{ __("You're all caught up. No applications need a decision.") }}
                @endif

                <x-slot:action>
                    <a href="{{ route('admin.scholarships.create') }}"
                       class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 sm:self-auto">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" d="M10 4v12M4 10h12" />
                        </svg>
                        {{ __('Post scholarship') }}
                    </a>
                </x-slot:action>
            </x-welcome-banner>

            <dl class="mt-6 grid gap-6 md:grid-cols-3">
                <x-stat-card :label="__('Total scholarships')" :value="number_format($scholarshipCount)">
                    <x-slot:icon>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7l-4-4Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v4h4M9 12h6M9 16h6" />
                        </svg>
                    </x-slot:icon>

                    <span class="tabular-nums">{{ __(':count open', ['count' => $openCount]) }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="tabular-nums">{{ __(':count closed', ['count' => $closedCount]) }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="tabular-nums">{{ __(':count ended', ['count' => $endedCount]) }}</span>
                </x-stat-card>

                <x-stat-card :label="__('Students with Active status')" :value="number_format($activeStudentCount)" tone="blue">
                    <x-slot:icon>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <circle cx="9" cy="8" r="3" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.5M17.5 19a5.5 5.5 0 0 0-2.5-4.6" />
                        </svg>
                    </x-slot:icon>

                    {{ trans_choice('of :count registered student|of :count registered students', $registeredStudentCount, ['count' => number_format($registeredStudentCount)]) }}
                </x-stat-card>

                <x-stat-card :label="__('Students on leave of absence')" :value="number_format($onLeaveStudentCount)" tone="gold">
                    <x-slot:icon>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M9.5 7v10M14.5 7v10" />
                        </svg>
                    </x-slot:icon>

                    {{ __('Free tuition and applying paused') }}
                </x-stat-card>
            </dl>

            {{--
                The two newest postings, newest first, drawn to the mock OSAS supplied
                (decided 2026-10-24). The mock's Provider column is Type, since
                a scholarship records no provider, and its only action is View:
                OSAS asked for no Edit here. It shows even when nothing is
                posted yet.
            --}}
            <section aria-labelledby="postings-heading" class="mt-10 overflow-hidden rounded-lg border border-rule bg-white">
                <div class="flex items-center justify-between gap-4 px-5 py-4">
                    <h2 id="postings-heading" class="flex items-center gap-2.5 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v16h16M7 15l4-4 3 3 5-5M16 9h3v3" />
                        </svg>
                        {{ __('Recent scholarship postings') }}
                    </h2>
                    <a href="{{ route('admin.scholarships.index') }}" class="shrink-0 rounded font-semibold text-seal hover:text-seal-deep hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-seal">{{ __('View all') }}</a>
                </div>

                {{-- Relative, so the sr-only labels in its rows stay inside the scroll instead of widening a phone's page. --}}
                <div class="relative overflow-x-auto">
                    <table class="w-full min-w-[48rem]">
                        <thead>
                            <tr class="bg-linen-sunk text-left text-graphite">
                                <th scope="col" class="py-2.5 pl-5 pr-4 font-medium">{{ __('Scholarship') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Type') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Deadline') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Status') }}</th>
                                <th scope="col" class="py-2.5 pl-4 pr-5 font-medium">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule">
                            @forelse ($postings as $posting)
                                <tr>
                                    <td class="py-3 pl-5 pr-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-seal-tint/60 text-seal" aria-hidden="true">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                                    @if ($posting->type === \App\Enums\ScholarshipType::Government)
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 10h16M12 3 4 7v3h16V7l-8-4ZM6 10v8M10 10v8M14 10v8M18 10v8" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4 2.5 9 12 14l9.5-5L12 4Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 11v4.5c0 1.4 2.7 3 6 3s6-1.6 6-3V11M21.5 9v5" />
                                                    @endif
                                                </svg>
                                            </span>
                                            <div class="min-w-0">
                                                <a href="{{ route('admin.scholarships.show', $posting) }}" class="rounded font-semibold text-ink [overflow-wrap:anywhere] hover:text-seal focus:outline-none focus-visible:ring-2 focus-visible:ring-seal">{{ $posting->title }}</a>
                                                <p class="max-w-[24rem] truncate text-slate">{{ $posting->description }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-graphite">
                                        {{ $posting->type->label() }}
                                        @if ($posting->stem_courses_only)
                                            <span class="block text-slate">{{ __('STEM only') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 tabular-nums text-graphite">
                                        @if ($posting->deadline)
                                            {{ $posting->deadline->format('M j, Y') }}
                                        @else
                                            <span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No deadline') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($posting->status === \App\Enums\ScholarshipStatus::Ended)
                                            <x-badge>{{ __('Ended') }}</x-badge>
                                        @elseif ($posting->isOpenForApplications())
                                            <x-badge color="green">{{ __('Open') }}</x-badge>
                                        @else
                                            <x-badge>{{ __('Closed') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap py-3 pl-4 pr-5">
                                        <a href="{{ route('admin.scholarships.show', $posting) }}" class="inline-flex items-center rounded-md border border-rule bg-white px-4 py-1.5 font-semibold text-seal transition duration-150 ease-in-out hover:border-seal focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('View') }}<span class="sr-only"> {{ $posting->title }}</span></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate">{{ __('Scholarships you post will be listed here.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
