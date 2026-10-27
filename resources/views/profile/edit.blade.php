{{--
    One compact, wide page for every role that fits one screen without
    scrolling (decided 2026-10-24 for staff; students took the same design on
    2026-10-25): the account's details in one card with a single Save. Staff
    also get the account's removal as one bar beneath.
--}}
<x-app-layout :title="__('Profile')" :sheet="true">
    <div class="px-6 py-6 lg:px-10">
        <div class="mx-auto max-w-[88rem]">
            <h1 data-vt-title class="text-lead font-semibold text-ink">{{ __('Profile') }}</h1>

            <div class="mt-4">
                @if ($user->isStudent())
                    @include('profile.partials.update-profile-information-form')
                @else
                    @include('profile.partials.staff-account-form')
                @endif
            </div>

            {{-- Students sign in with Google and have no password, and deleting an account is confirmed with one. --}}
            @unless ($user->isStudent())
                <div class="mt-4 rounded-lg border border-oxide/30 bg-white px-5 py-4">
                    @include('profile.partials.delete-user-form')
                </div>
            @endunless
        </div>
    </div>
</x-app-layout>
