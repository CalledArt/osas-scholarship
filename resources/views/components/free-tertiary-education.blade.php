@props(['linked' => true, 'yearOfStudy' => null, 'coveredYears' => null])

{{--
    Republic Act 10931 (Universal Access to Quality Tertiary Education Act):
    students hold it for their program's length plus one year, and it heads
    their Active scholarships until then. It is not an application and does
    not count toward User::SCHOLARSHIP_LIMIT.
    Set apart with the approved wash, since it is settled rather than pending.
    The tutorial passes linked=false, since its sample screens link nowhere.
--}}
<li>
    <{{ $linked ? 'a' : 'div' }} @if ($linked) href="{{ route('scholarships.republic-act-10931') }}" @endif
        {{ $attributes->class([
            'group flex items-center gap-4 bg-green-50 px-3 py-4',
            'transition duration-150 ease-in-out hover:bg-green-100/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cvsu-green' => $linked,
        ]) }}>
        <svg class="h-6 w-6 shrink-0 text-cvsu-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4 2.5 9 12 14l9.5-5L12 4Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 11v4.5c0 1.4 2.7 3 6 3s6-1.6 6-3V11M21.5 9v5" />
        </svg>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-cvsu-green-dark">{{ __('Republic Act No. 10931') }}</p>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Universal Access to Quality Tertiary Education Act') }}
                <span aria-hidden="true">&middot;</span>
                @if ($yearOfStudy !== null && $coveredYears !== null)
                    <span class="tabular-nums">{{ __('Year :year of :covered', ['year' => $yearOfStudy, 'covered' => $coveredYears]) }}</span>
                @else
                    {{ __('Free tuition and other school fees') }}
                @endif
            </p>
        </div>

        <x-badge class="shrink-0" color="green">{{ __('Active') }}</x-badge>

        <svg class="h-5 w-5 shrink-0 text-gray-500" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
        </svg>
    </{{ $linked ? 'a' : 'div' }}>
</li>
