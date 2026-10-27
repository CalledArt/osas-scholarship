{{--
    The student's scholarships under the dashboards' banner, as two panes of
    boxes: what they hold on the left, every scholarship still open to them on
    the right. Counts of years, places and slots left are deliberately not
    shown (decided 2026-10-24). With nothing open to apply to, the Open pane
    would be one sentence beside a short column, so the panes stack instead
    and the Mine boxes run across the width, as on the dashboard.
--}}
<x-app-layout :title="__('Scholarships')">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('Scholarships')">
                @if ($hasOpenScholarships)
                    <span class="font-semibold tabular-nums text-ink">{{ $availableScholarships->count() }}</span>
                    {{ trans_choice('scholarship is open to you.|scholarships are open to you.', $availableScholarships->count()) }}
                @else
                    {{ __('Nothing is open for you to apply to right now.') }}
                @endif
            </x-welcome-banner>

            <div @class([
                'mt-6 grid gap-6',
                'lg:grid-cols-[22rem_minmax(0,1fr)] lg:items-start' => $hasOpenScholarships,
            ])>
                <section aria-labelledby="standing-heading">
                    <h2 id="standing-heading" class="text-lead font-semibold text-ink">{{ __('Where you stand') }}</h2>

                    <div @class([
                        'mt-4 grid gap-4 sm:grid-cols-2',
                        'lg:grid-cols-1' => $hasOpenScholarships,
                    ])>
                        <x-active-scholarships :applications="$activeApplications" :student="Auth::user()" />
                    </div>
                </section>

                <x-available-scholarships :scholarships="$availableScholarships" :remaining-places="$remainingPlaces" :active-limit-reached="$activeLimitReached" :on-leave="$onLeave" />
            </div>
        </div>
    </div>
</x-app-layout>
