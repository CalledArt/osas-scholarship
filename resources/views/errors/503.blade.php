{{-- Maintenance mode answers before any session starts, so, like 500, this page asks nothing of the database. --}}
@php($heading = __('Down for a short update'))

<x-guest-layout :title="$heading">
    <x-error-sheet :heading="$heading" code="503">
        {{ __('The scholarship system is being updated. Try again in a few minutes.') }}

        <x-slot:actions>
            <a href="{{ url()->current() }}"
               class="inline-flex items-center justify-center rounded-sheet border border-transparent bg-seal px-4 py-2.5 font-semibold text-white shadow-seat transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2">
                {{ __('Try again') }}
            </a>
        </x-slot:actions>
    </x-error-sheet>
</x-guest-layout>
