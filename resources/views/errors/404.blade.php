@php($heading = __('That page is not here'))

@auth
    <x-app-layout :title="$heading">
        <x-error-sheet :heading="$heading" code="404">
            {{ __('The link may be out of date, or the record may have been removed by OSAS.') }}
        </x-error-sheet>
    </x-app-layout>
@else
    <x-guest-layout :title="$heading">
        <x-error-sheet :heading="$heading" code="404">
            {{ __('The link may be out of date. Sign in to see your own scholarships and applications.') }}
        </x-error-sheet>
    </x-guest-layout>
@endauth
