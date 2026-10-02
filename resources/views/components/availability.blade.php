@props(['scholarship'])

{{--
    Whether students can still apply, for staff: the first reason applications
    stopped, or the deadline while they are open. Never a deadline alone, which
    reads as open on a scholarship that is full or that OSAS closed early.
--}}
@switch($scholarship->availability())
    @case(\App\Enums\ScholarshipAvailability::ClosedByOsas)
        <span {{ $attributes }}>{{ __('Closed by OSAS') }}</span>
        @break
    @case(\App\Enums\ScholarshipAvailability::Full)
        <span {{ $attributes }}>{{ __('Full') }}</span>
        @break
    @default
        <x-deadline :scholarship="$scholarship" :attributes="$attributes" />
@endswitch
