{{--
    Something failed on the server, possibly the database itself, so this page
    asks nothing of it: the guest layout, and its own actions in place of the
    error sheet's signed-in ones.
--}}
@php($heading = __('Something went wrong on our side'))

<x-guest-layout :title="$heading">
    <x-error-sheet :heading="$heading" code="500">
        {{ __('What you just did may not have gone through. Try again in a moment, and if it keeps happening, tell the OSAS office what you were doing.') }}

        <x-slot:actions>
            <a href="{{ url()->previous(url('/')) }}"
               class="inline-flex items-center justify-center rounded-sheet border border-transparent bg-seal px-4 py-2.5 font-semibold text-white shadow-seat transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2">
                {{ __('Go back') }}
            </a>
            <a href="{{ url('/') }}"
               class="inline-flex items-center justify-center rounded-sheet border border-rule-strong bg-linen px-4 py-2 font-semibold text-graphite shadow-seat transition duration-150 ease-in-out hover:bg-linen-sunk focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">
                {{ __('Go to the start page') }}
            </a>
        </x-slot:actions>
    </x-error-sheet>
</x-guest-layout>
