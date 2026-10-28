{{--
    Every scholarship whose pass list has passed students, as record cards in
    the style of the student Scholarships page, under the dashboards' banner,
    which counts the funds still to set across every page. Each whole card
    leads into its fund allocation. The facts sit pinned to the card's foot so
    they line up across a row. Gold marks a fund still to set; green, one
    allocated.
--}}
<x-app-layout :title="__('Fund Allocation')" :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('Fund Allocation')">
                @if ($unfundedCount > 0)
                    <span class="font-semibold tabular-nums text-ink">{{ $unfundedCount }}</span>
                    {{ trans_choice('scholarship still needs its total funds.|scholarships still need their total funds.', $unfundedCount) }}
                @elseif ($scholarships->total() > 0)
                    {{ __("You're all caught up. Every fund is set.") }}
                @else
                    {{ __('No students have passed a scholarship yet.') }}
                @endif
            </x-welcome-banner>

            @if ($scholarships->isEmpty())
                <p class="mt-8 border-y border-rule py-6 text-slate">
                    {{ __('Scholarships appear here once a pass list approves someone. Each one\'s total funds are split equally among the students who passed.') }}
                </p>
            @else
                <ul data-results class="mt-8 grid gap-4 grid-cols-[repeat(auto-fill,minmax(min(20rem,100%),1fr))]" role="list">
                    @foreach ($scholarships as $scholarship)
                        @php
                            $totalFunds = $scholarship->totalFundsInCentavos();
                            $share = $scholarship->fundShareInCentavos($scholarship->passed_count);
                        @endphp
                        <li>
                            <a href="{{ route('admin.fund-allocation.show', $scholarship) }}"
 class="group flex h-full flex-col rounded-sheet border border-rule bg-white p-5 transition-colors duration-150 ease-in-out hover:border-seal/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">
                                <div class="flex items-start justify-between gap-3">
                                    <p data-vt-title class="min-w-0 text-lead font-semibold leading-snug text-ink [overflow-wrap:anywhere] group-hover:text-seal">{{ $scholarship->title }}</p>
                                    @if ($totalFunds === null)
                                        <x-badge color="gold" class="shrink-0">{{ __('Fund not set') }}</x-badge>
                                    @else
                                        <x-badge color="green" class="shrink-0">{{ __('Allocated') }}</x-badge>
                                    @endif
                                </div>
                                <p class="mt-1  text-slate">
                                    {{ $scholarship->type->label() }} <span aria-hidden="true">&middot;</span> {{ $scholarship->statusLabel() }}
                                </p>

                                <div class="mt-auto pt-5">
                                    <dl class="grid grid-cols-2 gap-3 border-t border-rule pt-4">
                                        <div class="min-w-0">
                                            <dt class="text-slate">{{ __('Total funds') }}</dt>
                                            <dd class="mt-0.5 text-lead font-semibold tabular-nums text-ink [overflow-wrap:anywhere]">
                                                {{ $totalFunds !== null ? \App\Support\Peso::format($totalFunds) : __('Not set') }}
                                            </dd>
                                        </div>
                                        <div class="min-w-0">
                                            <dt class="text-slate">{{ __('Per student') }}</dt>
                                            <dd class="mt-0.5 text-lead font-semibold tabular-nums text-ink [overflow-wrap:anywhere]">
                                                @if ($share !== null)
                                                    {{ \App\Support\Peso::format($share) }}
                                                @else
                                                    <span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No share until the fund is set') }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>

                                    <p class="mt-4  text-slate">
                                        <span class="font-semibold tabular-nums text-ink">{{ trans_choice(':count student passed|:count students passed', $scholarship->passed_count, ['count' => $scholarship->passed_count]) }}</span>
                                    </p>
                                </div>

                                <span class="mt-5  font-semibold text-seal" aria-hidden="true">{{ __('Open fund allocation') }} &rarr;</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($scholarships->hasPages())
                    <div class="mt-6">
                        {{ $scholarships->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
