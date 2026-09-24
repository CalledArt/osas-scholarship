@props(['scholarship', 'urgent' => false])

{{--
    A scholarship's deadline as whole calendar days ("Closes in 2 days"), with the
    exact date on hover. Never diffForHumans(): it truncates fractional days, so a
    date two days out would read as one.
--}}
@if ($scholarship->deadline)
    @php($days = $scholarship->daysUntilDeadline())
    <span title="{{ $scholarship->deadline->format('F j, Y') }}"
          {{ $attributes->class([$urgent && $days >= 0 && $days <= 3 ? 'font-semibold text-red-700' : '']) }}>
        @if ($days < 0)
            {{ trans_choice('Closed :count day ago|Closed :count days ago', abs($days), ['count' => abs($days)]) }}
        @elseif ($days === 0)
            {{ __('Closes today') }}
        @elseif ($days === 1)
            {{ __('Closes tomorrow') }}
        @else
            {{ __('Closes in :count days', ['count' => $days]) }}
        @endif
    </span>
@else
    <span {{ $attributes }}>{{ __('No deadline') }}</span>
@endif
