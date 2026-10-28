@props(['scholarship'])

{{--
    THE SEASON BAR — a closing date made measurable.

    The full width is the window from the day OSAS posted the scholarship to its
    deadline (the end of that day in Manila); the witness line marks where
    today sits in it. Once the deadline has passed the whole run is struck in
    Oxide and the witness line goes.

    It never carries the date on its own: the date is written out beside it. A
    degenerate window (a deadline on or before the posting date, which seed and
    imported data can both produce) draws nothing rather than a misleading full
    bar.
--}}
@if ($scholarship->deadline)
    @php
        $posted = $scholarship->created_at;
        $deadline = $scholarship->deadline;
        $lapsed = $scholarship->hasPassedDeadline();

        $span = $posted ? $scholarship->deadlineClosesAt()->getTimestamp() - $posted->getTimestamp() : 0;
        $through = $posted ? now()->getTimestamp() - $posted->getTimestamp() : 0;

        $measurable = $span > 0;
        $elapsed = $measurable ? max(0, min(100, $through / $span * 100)) : 0;
    @endphp

    @if ($measurable || $lapsed)
        <div {{ $attributes->merge(['class' => 'season']) }}
             @class(['season--lapsed' => $lapsed])
             style="--elapsed: {{ round($elapsed, 2) }}%"
             role="img"
             aria-label="{{ $lapsed
                 ? __('Applications closed on :date.', ['date' => $deadline->format('F j, Y')])
                 : __('Applications close on :date.', ['date' => $deadline->format('F j, Y')]) }}"></div>
    @endif
@endif
