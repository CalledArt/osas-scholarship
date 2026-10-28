{{--
    A form sent after its page sat open past the session's lifetime. Going back
    reloads the form with a fresh token, which is the one way through.
--}}
@php($heading = __('This form expired'))

@auth
    <x-app-layout :title="$heading">
        <x-error-sheet :heading="$heading" code="419">
            {{ __('The page sat open too long, so it was not sent. Go back, reload the page and send it again.') }}

            <x-slot:actions>
                <a href="{{ url()->previous(route('dashboard')) }}"
                   class="inline-flex items-center justify-center rounded-sheet border border-transparent bg-seal px-4 py-2.5 font-semibold text-white shadow-seat transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2">
                    {{ __('Go back') }}
                </a>
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                   class="inline-flex items-center justify-center rounded-sheet border border-rule-strong bg-linen px-4 py-2 font-semibold text-graphite shadow-seat transition duration-150 ease-in-out hover:bg-linen-sunk focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">
                    {{ __('Go to dashboard') }}
                </a>
            </x-slot:actions>
        </x-error-sheet>
    </x-app-layout>
@else
    <x-guest-layout :title="$heading">
        <x-error-sheet :heading="$heading" code="419">
            {{ __('You were signed out while the page sat open, so it was not sent. Sign in and try again.') }}
        </x-error-sheet>
    </x-guest-layout>
@endauth
