{{--
    Every application the student has sent, newest first, under the
    dashboards' banner, whose sentence counts what still waits on OSAS. Each
    is a card drawn like the admin Fund Allocation cards (asked 2026-10-25):
    the whole card opens the application, its status mark sits top right, the
    reference the office asks for and the date sent are pinned to the card's
    foot so they line up across a row, and a pending one states its wait.
--}}
<x-app-layout :title="__('My Applications')" :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('My Applications')">
                @if ($waitingApplicationCount > 0)
                    <span class="font-semibold tabular-nums text-ink">{{ $waitingApplicationCount }}</span>
                    {{ trans_choice('application is waiting for a decision.|applications are waiting for a decision.', $waitingApplicationCount) }}
                @elseif ($applications->total() > 0)
                    {{ __("You're all caught up.") }}
                @else
                    {{ __('Your applications appear here once you apply.') }}
                @endif
            </x-welcome-banner>

            @if (session('status'))
                <div class="mt-6 rounded-sheet border border-grant/30 bg-grant-wash px-4 py-3 text-grant-deep" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($applications->isEmpty())
                <p class="mt-8 border-y border-rule py-6 text-slate">
                    {{ __("You haven't applied to any scholarships yet.") }}
                    <a href="{{ route('scholarships.index') }}" class="rounded-sheet font-semibold text-seal underline underline-offset-4 hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Browse scholarships') }}</a>
                </p>
            @else
                <ul data-results class="mt-8 grid gap-4 grid-cols-[repeat(auto-fill,minmax(min(20rem,100%),1fr))]" role="list">
                    @foreach ($applications as $application)
                        @php($waited = $application->status === \App\Enums\ApplicationStatus::Pending ? (int) $application->submitted_at->copy()->startOfDay()->diffInDays(now()->startOfDay()) : null)
                        <li>
                            <a href="{{ route('scholarship-applications.show', $application) }}"
                               class="group flex h-full flex-col rounded-sheet border border-rule bg-white p-5 transition-colors duration-150 ease-in-out hover:border-seal/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">
                                <div class="flex items-start justify-between gap-3">
                                    <p data-vt-title class="min-w-0 text-lead font-semibold leading-snug text-ink [overflow-wrap:anywhere] group-hover:text-seal">{{ $application->scholarship->title }}</p>
                                    <x-badge class="shrink-0" :color="match ($application->status->value) {
                                        'approved' => 'green',
                                        'rejected' => 'red',
                                        'voided' => 'gray',
                                        default => 'gold',
                                    }">
                                        {{ $application->status->label() }}
                                    </x-badge>
                                </div>
                                <p class="mt-1 text-slate">
                                    {{ $application->scholarship->type->label() }} <span aria-hidden="true">&middot;</span> {{ $application->scholarship->status->label() }}
                                </p>

                                <div class="mt-auto pt-5">
                                    <dl class="grid grid-cols-2 gap-3 border-t border-rule pt-4">
                                        <div class="min-w-0">
                                            <dt class="text-slate">{{ __('Reference') }}</dt>
                                            <dd class="mt-0.5 font-mono text-lead text-ink [overflow-wrap:anywhere]">{{ $application->reference }}</dd>
                                        </div>
                                        <div class="min-w-0">
                                            <dt class="text-slate">{{ __('Applied') }}</dt>
                                            <dd class="mt-0.5 text-lead font-semibold text-ink">
                                                <time datetime="{{ $application->submitted_at->toDateString() }}">{{ $application->submitted_at->format('M j, Y') }}</time>
                                            </dd>
                                        </div>
                                    </dl>

                                    @if ($waited !== null)
                                        <p class="mt-4 font-semibold tabular-nums text-ink">{{ trans_choice(':count day waiting|:count days waiting', $waited, ['count' => $waited]) }}</p>
                                    @endif
                                </div>

                                <span class="mt-5 font-semibold text-seal" aria-hidden="true">{{ __('View application') }} &rarr;</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($applications->hasPages())
                    <div class="mt-6">
                        {{ $applications->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
