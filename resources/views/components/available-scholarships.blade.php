@props(['scholarships', 'remainingPlaces'])

{{--
    The scholarships a student can apply to right now, under the limit on how
    many they may hold. At the limit the list gives way to how to free a place,
    since nothing in it could be applied to. The slot is for a trailing link.
--}}
<section aria-labelledby="available-heading" {{ $attributes }}>
    <h2 id="available-heading" class="text-base font-semibold text-gray-900">{{ __('Available to apply') }}</h2>

    <p class="mt-1 text-sm text-gray-500">
        {{ __('You can hold up to :limit scholarships at a time, counting applications waiting for a decision.', ['limit' => \App\Models\User::SCHOLARSHIP_LIMIT]) }}
        @if ($remainingPlaces > 0)
            <span class="font-semibold tabular-nums text-gray-900">{{ __('You can apply to :count more.', ['count' => $remainingPlaces]) }}</span>
        @endif
    </p>

    @if ($remainingPlaces === 0)
        <p class="mt-3 border-y border-gray-200 py-6 text-sm text-gray-500">
            {{ __("You've reached the limit. You can apply again once an application is rejected or one of your scholarships ends.") }}
        </p>
    @elseif ($scholarships->isEmpty())
        <p class="mt-3 border-y border-gray-200 py-6 text-sm text-gray-500">
            {{ __('No scholarships are accepting applications from you right now. New ones will appear here when OSAS posts them.') }}
        </p>
    @else
        <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
            @foreach ($scholarships as $scholarship)
                <li>
                    <a href="{{ route('scholarships.show', $scholarship) }}"
                       class="group flex items-center gap-4 px-3 py-4 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cvsu-green">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere] group-hover:text-cvsu-green">{{ $scholarship->title }}</p>
                            <p class="mt-1 text-sm text-gray-500">
                                <x-deadline :scholarship="$scholarship" :urgent="true" />
                                <span aria-hidden="true">&middot;</span>
                                <span class="tabular-nums">{{ trans_choice(':count slot left|:count slots left', $scholarship->remainingSlots(), ['count' => $scholarship->remainingSlots()]) }}</span>
                            </p>
                        </div>

                        <span class="hidden shrink-0 text-sm font-semibold text-cvsu-green sm:inline">{{ __('Apply') }} &rarr;</span>
                        <svg class="h-5 w-5 shrink-0 text-gray-500 sm:hidden" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
                        </svg>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    {{ $slot }}
</section>
