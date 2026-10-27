@props(['active' => false])

@php
    $classes = $active
        ? 'bg-seal-deep font-semibold text-white'
        : 'font-medium text-seal-tint hover:bg-seal-deep/60 hover:text-white';
@endphp

{{-- data-rail-link lets a press move the active plate before the page answers (x-page-skeleton). --}}
<a data-rail-link {{ $attributes->merge(['class' => 'block truncate rounded-sheet px-3 py-2 transition duration-150 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 focus-visible:ring-offset-seal '.$classes]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
