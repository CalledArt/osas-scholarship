{{--
    Every posted scholarship under the dashboards' banner, as cards drawn like
    the Fund Allocation cards (asked 2026-10-25). The title is the card's way
    in and stretches over the whole card; Edit sits above that stretch so it
    stays its own control. Slots left and Applicants are the figures OSAS
    compares, pinned to the card's foot so they line up across a row.
--}}
<x-app-layout :title="__('Manage Scholarships')" :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('Manage Scholarships')">
                @if ($scholarships->total() > 0)
                    <span class="font-semibold tabular-nums text-ink">{{ $scholarships->total() }}</span>
                    {{ trans_choice('scholarship posted.|scholarships posted.', $scholarships->total()) }}
                @else
                    {{ __('No scholarships have been posted yet.') }}
                @endif

                <x-slot:action>
                    <a href="{{ route('admin.scholarships.create') }}"
                       class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 sm:self-auto">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" d="M10 4v12M4 10h12" />
                        </svg>
                        {{ __('Post Scholarship') }}
                    </a>
                </x-slot:action>
            </x-welcome-banner>

            @if (session('status'))
                <div class="mt-6 rounded-sheet border border-grant/30 bg-grant-wash px-4 py-3 text-grant-deep" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($scholarships->isNotEmpty())
                <ul data-results class="mt-8 grid gap-4 grid-cols-[repeat(auto-fill,minmax(min(20rem,100%),1fr))]" role="list">
                    @foreach ($scholarships as $scholarship)
                        <li>
                            <article class="group relative flex h-full flex-col rounded-sheet border border-rule bg-white p-5 transition-colors duration-150 ease-in-out hover:border-seal/40">
                                <div class="flex items-start justify-between gap-3">
                                    <h2 class="min-w-0 text-lead font-semibold leading-snug text-ink [overflow-wrap:anywhere]">
                                        <a href="{{ route('admin.scholarships.show', $scholarship) }}" data-vt-title
                                           class="rounded after:absolute after:inset-0 after:content-[''] group-hover:text-seal focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ $scholarship->title }}</a>
                                    </h2>
                                    <x-badge class="shrink-0" :color="$scholarship->status->value === 'open' ? 'green' : 'gray'">
                                        {{ $scholarship->status->label() }}
                                    </x-badge>
                                </div>
                                <p class="mt-1 text-slate">
                                    {{ $scholarship->type->label() }}
                                    @if ($scholarship->stem_courses_only)
                                        <span aria-hidden="true">&middot;</span> {{ __('STEM only') }}
                                    @endif
                                </p>

                                <div class="mt-auto pt-5">
                                    <dl class="grid grid-cols-2 gap-3 border-t border-rule pt-4">
                                        <div class="min-w-0">
                                            <dt class="text-slate">{{ __('Slots left') }}</dt>
                                            <dd class="mt-0.5 text-lead font-semibold tabular-nums text-ink">{{ __(':left of :total', ['left' => $scholarship->remainingSlots(), 'total' => $scholarship->slots]) }}</dd>
                                        </div>
                                        <div class="min-w-0">
                                            <dt class="text-slate">{{ __('Applicants') }}</dt>
                                            <dd class="mt-0.5 text-lead font-semibold tabular-nums text-ink">{{ $scholarship->applications_count }}</dd>
                                        </div>
                                    </dl>

                                    <p class="mt-4 font-semibold tabular-nums text-ink">
                                        {{-- The mark is the status OSAS set; past its deadline a scholarship marked Open takes no applications, so the date says so. --}}
                                        @if ($scholarship->deadline)
                                            {{ $scholarship->hasPassedDeadline() ? __('Past deadline') : __('Closes') }} <time datetime="{{ $scholarship->deadline->toDateString() }}">{{ $scholarship->deadline->format('M j, Y') }}</time>
                                        @else
                                            {{ __('No deadline') }}
                                        @endif
                                    </p>
                                </div>

                                <div class="mt-5 flex items-center justify-between gap-4">
                                    <span class="font-semibold text-seal" aria-hidden="true">{{ __('View scholarship') }} &rarr;</span>
                                    <a href="{{ route('admin.scholarships.edit', $scholarship) }}" class="relative z-10 rounded font-medium text-graphite underline-offset-4 hover:text-ink hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Edit') }}<span class="sr-only"> {{ $scholarship->title }}</span></a>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($scholarships->hasPages())
                <div class="mt-6">
                    {{ $scholarships->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
