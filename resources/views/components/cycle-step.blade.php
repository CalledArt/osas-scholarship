@props(['step', 'date' => null, 'tone' => null, 'current' => false])

{{--
    One stage of the scholarship cycle (Post, Apply, Review, Decide), filled in
    with one student's own record. `date` is when the stage happened, so a stage
    still ahead carries none — which is what lets the register teach the
    procedure before the student has any record at all.

    `tone` lights the stage the student's attention belongs on: Plot once a
    decision is settled in their favour, Stake while it is still waiting on a
    human, Oxide once it has gone against them. The previous system marked the
    waiting stage with its faintest surface; waiting on a human decision is
    exactly what this system's Stake role is for.
--}}
<li @if ($current) aria-current="step" @endif {{ $attributes->class([
    'grid grid-cols-[4.5rem_minmax(0,1fr)] items-baseline gap-x-4 gap-y-1 px-6 py-4 sm:grid-cols-[6rem_minmax(0,1fr)_auto]',
    'bg-grant-wash' => $tone === 'plot',
    'bg-gold-wash' => $tone === 'stake',
    'bg-oxide-wash' => $tone === 'oxide',
]) }}>
    <span @class([
        'font-annot text-annot uppercase',
        'text-grant-deep' => $tone === 'plot',
        'text-oxide-deep' => $tone === 'oxide',
        'text-ink' => $tone === 'stake',
        'text-slate' => $tone === null,
    ])>{{ $step }}</span>

    <div @class(['min-w-0', 'text-ink' => $tone !== null, 'text-slate' => $tone === null])>{{ $slot }}</div>

    @if ($date)
        <time datetime="{{ $date->toDateString() }}" class="col-start-2 font-annot text-annot uppercase text-slate sm:col-start-3 sm:text-right">{{ $date->format('M j, Y') }}</time>
    @endif
</li>
