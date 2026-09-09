@props(['active' => false])

@php
    $classes = $active
        ? 'bg-white font-semibold text-gray-900 shadow-sm'
        : 'font-medium text-gray-600 hover:bg-gray-200/60 hover:text-gray-900';
@endphp

<a {{ $attributes->merge(['class' => 'block truncate rounded-md px-3 py-2 text-sm transition duration-150 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2 focus-visible:ring-offset-gray-100 '.$classes]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
