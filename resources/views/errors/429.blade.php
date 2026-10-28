@php($heading = __('Too many tries'))

@auth
    <x-app-layout :title="$heading">
        <x-error-sheet :heading="$heading" code="429">
            {{ __('That was asked for too many times in a short while. Wait a minute, then try again.') }}
        </x-error-sheet>
    </x-app-layout>
@else
    <x-guest-layout :title="$heading">
        <x-error-sheet :heading="$heading" code="429">
            {{ __('That was asked for too many times in a short while. Wait a minute, then try again.') }}
        </x-error-sheet>
    </x-guest-layout>
@endauth
