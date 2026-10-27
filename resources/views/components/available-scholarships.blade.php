@props(['scholarships', 'remainingPlaces', 'activeLimitReached' => false, 'onLeave' => false, 'count' => null])

{{--
    The scholarships a student can apply to right now, as a grid of boxes for
    the page's "Open" pane, soonest closing first. Every box sets the same
    facts in the same cells (when it closes, slots left) so two can be compared
    at a glance, and the whole box opens the scholarship, where the student
    applies. While nothing can be applied to, one box says so and the places
    box beside it says why, so the reason is stated once. The slot is for a
    trailing link.

    `held_slots_count` is eager-loaded by Scholarship::availableTo(), so the
    slot squares cost no query per box; `required_documents_count` rides along
    as a subquery where the page loaded it. `count` is the full number
    available when the page shows only some of them.
--}}
@php($isBlocked = $onLeave || $activeLimitReached || $remainingPlaces === 0)

<section aria-labelledby="available-heading" {{ $attributes }}>
    <h2 id="available-heading" class="text-lead font-semibold text-ink">
        {{ __('Available to apply') }}
        @if (! $isBlocked && $scholarships->isNotEmpty())
            <span class="font-normal text-slate">({{ $count ?? $scholarships->count() }})</span>
        @endif
    </h2>

    @if ($isBlocked || $scholarships->isEmpty())
        <p class="mt-4 border border-rule bg-white px-5 py-8 text-center text-slate shadow-seat">
            @if ($onLeave)
                {{ __('On leave of absence (LOA): applying is paused.') }}
            @elseif ($activeLimitReached)
                {{ __('You hold an active scholarship. Apply again once it ends.') }}
            @elseif ($isBlocked)
                {{ __('You already have :limit applications waiting for a decision. You can apply again once one of them is rejected.', ['limit' => \App\Models\User::SCHOLARSHIP_LIMIT]) }}
            @else
                {{ __('No scholarships are open to you right now. New ones appear here when OSAS posts them.') }}
            @endif
        </p>
    @else
        <ul class="mt-4 grid gap-4 sm:grid-cols-2" role="list">
            @foreach ($scholarships as $scholarship)
                <li class="flex">
                    <a href="{{ route('scholarships.show', $scholarship) }}"
                       class="group flex w-full flex-col border border-rule bg-white shadow-seat transition duration-150 ease-in-out hover:border-seal focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2 focus-visible:ring-offset-linen">
                        <div class="flex-1 px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <h3 data-vt-title class="font-semibold text-ink [overflow-wrap:anywhere] group-hover:text-seal">{{ $scholarship->title }}</h3>
                                <x-badge class="shrink-0">{{ $scholarship->type->label() }}</x-badge>
                            </div>
                        </div>

                        <dl class="border-t border-rule">
                            <div class="px-5 py-2.5">
                                <dt class="font-annot text-annot uppercase text-slate">{{ __('Closes') }}</dt>
                                <dd class="text-ink">
                                    @if ($scholarship->deadline)
                                        <time datetime="{{ $scholarship->deadline->toDateString() }}">{{ $scholarship->deadline->format('M j, Y') }}</time>
                                        <span class="block text-slate"><x-deadline :scholarship="$scholarship" :urgent="true" /></span>
                                    @else
                                        {{ __('No deadline') }}
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <div class="flex items-center justify-between gap-3 border-t border-rule px-5 py-2.5">
                            <span class="text-slate">
                                @isset($scholarship->required_documents_count)
                                    {{ trans_choice('{0} No documents|{1} :count document|[2,*] :count documents', $scholarship->required_documents_count, ['count' => $scholarship->required_documents_count]) }}
                                @endisset
                            </span>
                            <span class="rounded-sheet bg-seal px-4 py-1.5 font-semibold text-white transition duration-150 ease-in-out group-hover:bg-seal-deep" aria-hidden="true">{{ __('Apply') }}</span>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    {{ $slot }}
</section>
