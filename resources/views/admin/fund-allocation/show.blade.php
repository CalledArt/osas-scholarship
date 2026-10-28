{{--
    One scholarship's fund allocation, in the order OSAS works it: the total
    funds, entered here and divided live by the students who passed to show
    each one's allocation; where the fund stands; then every student who
    passed with their share. The Excel sheet lists the same students with
    their share as the Amount Received and a blank Signature column each
    student signs when paid. The fund form locks on submit, since a second press
    would only repeat the request. From lg the page fits the screen and only the
    student list scrolls, under a header that stays in view. The title, its
    type and status, and the page's two actions sit in the dashboards' banner.
--}}
<x-app-layout :title="$scholarship->title" :sheet="true">
    <div class="px-6 py-10 lg:flex lg:h-screen lg:flex-col lg:px-10 lg:py-6">
        <div class="mx-auto max-w-[100rem] lg:flex lg:min-h-0 lg:w-full lg:flex-col">
            <x-back-link :href="route('admin.fund-allocation.index')" class="lg:self-start">{{ __('Back to fund allocation') }}</x-back-link>

            <div class="mt-6 lg:mt-4">
                <x-welcome-banner :heading="$scholarship->title" :vt-title="true">
                    <x-badge class="me-2" :color="$scholarship->status->value === 'open' ? 'green' : 'gray'">
                        {{ $scholarship->status->label() }}
                    </x-badge>
                    {{ $scholarship->type->label() }}

                    <x-slot:action>
                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <a href="{{ route('admin.scholarships.show', $scholarship) }}" class="inline-flex items-center rounded-sheet border border-rule-strong bg-white px-4 py-2 font-semibold text-graphite shadow-sm transition duration-150 ease-in-out hover:bg-linen-sunk focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('View scholarship') }}</a>
                            <a href="{{ route('admin.fund-allocation.report', $scholarship) }}" download class="inline-flex items-center justify-center rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2">{{ __('Download Excel sheet') }}</a>
                        </div>
                    </x-slot:action>
                </x-welcome-banner>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-sheet border border-grant/30 bg-grant-wash px-4 py-3  text-grant-deep" role="status">
                    {{ session('status') }}
                </div>
            @endif

            {{-- The allocation previews the split the server would accept and make: ₱1 to the column's maximum, whole centavos, rounded down. --}}
            @php
                $passedCount = $passedApplications->count();
                $totalFunds = $scholarship->totalFundsInCentavos();
                $fundButtonLabel = $totalFunds === null ? __('Set total funds') : __('Update total funds');
                $currentShare = $share !== null ? \App\Support\Peso::format($share) : null;
            @endphp
            <form method="POST" action="{{ route('admin.fund-allocation.funds.update', $scholarship) }}" class="mt-8 rounded-sheet border border-rule bg-white p-5 lg:mt-6"
 x-data="{
                      total: @js((string) old('total_funds', $scholarship->total_funds ?? '')),
                      students: @js($passedCount),
                      sending: false,
                      allocation() {
                          const centavos = Math.round(parseFloat(this.total) * 100)
                          if (this.students < 1 || ! (centavos >= 100 && centavos <= 999999999999)) return null
                          return '₱' + (Math.floor(centavos / this.students) / 100).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                      },
                  }"
                  @submit="sending = true"
                  @pageshow.window="if ($event.persisted) sending = false">
                @csrf
                @method('PUT')

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="sm:w-64">
                        <x-input-label for="total_funds" :value="__('Total funds (₱)')" />
                        <x-text-input id="total_funds" name="total_funds" type="number" min="1" max="9999999999.99" step="0.01" inputmode="decimal" x-model="total" :value="old('total_funds', $scholarship->total_funds)" required aria-describedby="total-funds-help" x-bind:readonly="sending" class="mt-1 block w-full tabular-nums" />
                    </div>

                    @if ($passedCount === 0)
                        <p class="text-slate sm:pb-2.5">{{ __('No students have passed yet. Each one\'s allocation appears here once a pass list approves them.') }}</p>
                    @else
                        <p class="min-w-0  text-slate sm:pb-2.5" aria-live="polite" aria-atomic="true">
                            {{ trans_choice('÷ :count student who passed =|÷ :count students who passed =', $passedCount, ['count' => $passedCount]) }}
                            <span class="font-semibold tabular-nums text-ink" x-show="allocation() !== null" x-text="allocation()" @if ($currentShare === null) x-cloak @endif>{{ $currentShare }}</span>
                            <span x-show="allocation() === null" @if ($currentShare !== null) x-cloak @endif><span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('no amount until a valid total is entered') }}</span></span>
                            {{ __('per student') }}
                        </p>
                    @endif

                    <x-primary-button class="sm:ms-auto" x-bind:disabled="sending">
                        <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="sending ? @js(__('Saving...')) : @js($fundButtonLabel)">{{ $fundButtonLabel }}</span>
                    </x-primary-button>
                </div>

                <p id="total-funds-help" class="mt-3 max-w-prose  text-slate">
                    {{ __('Divided equally among the students who passed, rounded down to the centavo.') }}
                </p>
                <x-input-error :messages="$errors->get('total_funds')" class="mt-2" />
            </form>

            <dl class="mt-8 grid gap-px overflow-hidden rounded-sheet border border-rule bg-rule sm:grid-cols-3 lg:mt-4 lg:shrink-0">
                <div class="bg-white px-4 py-4">
                    <dt class="font-annot text-annot uppercase text-slate">{{ __('Total funds') }}</dt>
                    <dd class="mt-1 text-lead font-semibold tabular-nums text-ink">{{ $totalFunds !== null ? \App\Support\Peso::format($totalFunds) : __('Not set') }}</dd>
                </div>
                <div class="bg-white px-4 py-4">
                    <dt class="font-annot text-annot uppercase text-slate">{{ __('Per student') }}</dt>
                    <dd class="mt-1 text-lead font-semibold tabular-nums text-ink">
                        @if ($currentShare !== null)
                            {{ $currentShare }}
                        @else
                            <span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No share until the fund is set') }}</span>
                        @endif
                    </dd>
                    <dd class="text-slate">{{ trans_choice(':count student passed|:count students passed', $passedCount, ['count' => $passedCount]) }}</dd>
                </div>
                <div class="bg-white px-4 py-4">
                    <dt class="font-annot text-annot uppercase text-slate">{{ __('Allocated') }}</dt>
                    <dd class="mt-1 text-lead font-semibold tabular-nums text-ink">
                        @if ($share !== null)
                            {{ \App\Support\Peso::format($share * $passedCount) }}
                        @else
                            <span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('Nothing allocated until the fund is set') }}</span>
                        @endif
                    </dd>
                    @if ($share !== null && $totalFunds > $share * $passedCount)
                        <dd class="tabular-nums text-slate">{{ __(':remainder left over stays in the fund', ['remainder' => \App\Support\Peso::format($totalFunds - $share * $passedCount)]) }}</dd>
                    @endif
                </div>
            </dl>

            <section aria-labelledby="students-heading" class="mt-10 lg:mt-6 lg:flex lg:min-h-0 lg:flex-col">
                <h2 id="students-heading" class="text-lead font-semibold text-ink">
                    {{ __('Students who passed') }} <span class="font-normal tabular-nums text-slate">({{ $passedCount }})</span>
                </h2>

                @if ($passedApplications->isEmpty())
                    {{-- Said beneath the heading, not as a row of the table: the table is wider than a phone and scrolls sideways, which cut the sentence off. --}}
                    <p class="mt-3 border-y border-rule py-8 text-center text-slate">
                        {{ __('No students have passed this scholarship yet. They appear here once a pass list approves them.') }}
                    </p>
                @else
                    {{-- Its rows hold no links, so the scroll itself takes focus and arrow keys can move it. A group, not a second region: the section around it is already the landmark of that name. --}}
                    <div tabindex="0" role="group" aria-labelledby="students-heading" class="relative mt-3 overflow-x-auto lg:min-h-0 lg:overflow-y-auto focus-visible:-outline-offset-2">
                        <table class="w-full min-w-[44rem] ">
                            <thead class="sticky top-0 z-10 bg-white">
                                <tr class="whitespace-nowrap font-annot text-annot uppercase text-slate [&>th]:shadow-[inset_0_-1px_0_theme(colors.rule.DEFAULT)]">
                                    <th scope="col" class="py-2 pr-4 text-left font-medium">{{ __('Name') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Student no.') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Course') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Year level') }}</th>
                                    <th scope="col" class="py-2 pl-4 text-right font-medium">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($passedApplications as $application)
                                    <tr class="border-b border-rule align-top">
                                        <td class="py-3 pr-4 font-medium uppercase text-ink [overflow-wrap:anywhere]">{{ $application->user->surname_first_name }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-slate">@if ($application->user->student_number){{ $application->user->student_number }}@else<span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No student number') }}</span>@endif</td>
                                        <td class="px-4 py-3 text-slate [overflow-wrap:anywhere]">@if ($application->user->course){{ $application->user->course }}@else<span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No course recorded') }}</span>@endif</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate">@if ($application->user->year_level){{ __('Year :level', ['level' => $application->user->year_level]) }}@else<span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No year level recorded') }}</span>@endif</td>
                                        <td class="whitespace-nowrap py-3 pl-4 text-right tabular-nums text-ink">
                                            @if ($currentShare !== null)
                                                {{ $currentShare }}
                                            @else
                                                <span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No share until the fund is set') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
