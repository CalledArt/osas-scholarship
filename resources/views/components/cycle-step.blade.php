@props(['step', 'date' => null, 'tone' => null])

{{--
    One step of the scholarship cycle the sign-in page lays out (Post, Apply,
    Review, Decide), filled in with one student's own record. `date` is when
    the step happened, so a step still ahead carries none. `tone` lights the
    step the student's attention belongs on, the way Republic Act 10931 is
    lit in Active scholarships: the approved wash once a decision is settled
    in their favour, Ledger Gray otherwise. Gold stays in the pending pill.
--}}
<li {{ $attributes->class([
    'grid grid-cols-[4.5rem_minmax(0,1fr)] items-baseline gap-x-4 gap-y-1 px-3 py-4 text-sm sm:grid-cols-[5.5rem_minmax(0,1fr)_auto]',
    'bg-green-50' => $tone === 'green',
    'bg-gray-50' => $tone === 'gray',
]) }}>
    <span @class(['font-semibold', 'text-cvsu-green-dark' => $tone === 'green', 'text-gray-900' => $tone !== 'green'])>{{ $step }}</span>

    <div @class(['min-w-0', 'text-gray-700' => $tone !== null, 'text-gray-600' => $tone === null])>{{ $slot }}</div>

    @if ($date)
        <time datetime="{{ $date->toDateString() }}" class="col-start-2 tabular-nums text-gray-500 sm:col-start-3 sm:text-right">{{ $date->format('M j, Y') }}</time>
    @endif
</li>
