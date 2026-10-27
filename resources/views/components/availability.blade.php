@props(['scholarship', 'urgent' => false])

{{--
    Whether students can still apply: the first reason applications stopped, or
    the deadline while they are open. Never a deadline alone, which reads as
    open on a scholarship that is full or that OSAS closed early.

    An ended grant period is told apart from a closure, because
    Scholarship::availability() folds both into ClosedByOsas and they are not
    the same fact: Ended means the grant is over and has freed its holders'
    places, Closed means OSAS merely stopped taking applications.
--}}
@if ($scholarship->status === \App\Enums\ScholarshipStatus::Ended)
    <span {{ $attributes }}>{{ __('Grant period ended') }}</span>
@else
    @switch($scholarship->availability())
        @case(\App\Enums\ScholarshipAvailability::ClosedByOsas)
            <span {{ $attributes }}>{{ __('Closed by OSAS') }}</span>
            @break
        @case(\App\Enums\ScholarshipAvailability::Full)
            <span {{ $attributes }}>{{ __('Full') }}</span>
            @break
        @default
            <x-deadline :scholarship="$scholarship" :urgent="$urgent" :attributes="$attributes" />
    @endswitch
@endif
