@props(['scholarship', 'urgent' => false])

{{--
    A scholarship's deadline as whole calendar days ("Closes in 2 days"), with the
    exact date on hover. Never diffForHumans(): it truncates fractional days, so a
    date two days out would read as one. Whether it has closed comes from
    hasPassedDeadline(), the check that actually stops applications.

    An urgent deadline is carried by colour and weight, so it also carries a
    spelled-out companion — the urgency is lost on anyone who cannot see the
    Oxide.
--}}
@if ($scholarship->deadline)
    @php($days = $scholarship->daysUntilDeadline())
    @php($hasClosed = $scholarship->hasPassedDeadline())
    @php($isUrgent = $urgent && ! $hasClosed && $days <= 3)
    <span title="{{ $scholarship->deadline->format('F j, Y') }}"
          {{ $attributes->class([$isUrgent ? 'font-semibold text-oxide' : '']) }}>
        @if ($hasClosed && $days === 0)
            {{ __('Closed today') }}
        @elseif ($hasClosed)
            {{ trans_choice('Closed :count day ago|Closed :count days ago', abs($days), ['count' => abs($days)]) }}
        @elseif ($days === 0)
            {{ __('Closes today') }}
        @elseif ($days === 1)
            {{ __('Closes tomorrow') }}
        @else
            {{ __('Closes in :count days', ['count' => $days]) }}
        @endif
        @if ($isUrgent)
            <span class="sr-only">{{ __('Closing soon.') }}</span>
        @endif
    </span>
@else
    <span {{ $attributes }}>{{ __('No deadline') }}</span>
@endif
