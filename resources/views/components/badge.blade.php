@props(['color' => 'gray'])

{{--
    A status mark is a stamp on a drawing: rectangular, uppercase annotation,
    read-only. Never a filter, never a button, never dismissible. The prop names
    are kept from the previous system so call sites did not all have to change.
--}}
@php
    $colors = [
        'gray' => 'bg-linen-sunk text-graphite',
        'green' => 'bg-grant-wash text-grant-deep',
        'red' => 'bg-oxide-wash text-oxide-deep',
        'gold' => 'bg-gold-wash text-ink',
        // For a mark that sits on a box already washed in its state's colour.
        'paper' => 'bg-white text-ink',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-sheet px-2 py-0.5 font-annot text-annot uppercase '.($colors[$color] ?? $colors['gray'])]) }}>
    {{ $slot }}
</span>
