@php($heading = __('You cannot open this'))

@auth
    <x-app-layout :title="$heading">
        <x-error-sheet :heading="$heading" code="403">
            {{ __('This might be an application you have already sent, a scholarship that stopped taking applications, or a record that belongs to someone else. Your own applications are always listed under My Applications.') }}
        </x-error-sheet>
    </x-app-layout>
@else
    <x-guest-layout :title="$heading">
        <x-error-sheet :heading="$heading" code="403">
            {{ __('Sign in with your CvSU Google account to see your own scholarships and applications.') }}
        </x-error-sheet>
    </x-guest-layout>
@endauth
